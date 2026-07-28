<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Puesto;
use Illuminate\Database\Seeder;

class EmpleadoSeeder extends Seeder
{
    /**
     * Seed employee demo data.
     */
    public function run(): void
    {
        $departamentos = [
            'Recursos Humanos',
            'Finanzas',
            'Operaciones',
            'Ventas',
            'Tecnologia',
        ];

        $puestos = [
            'Analista de nomina',
            'Auxiliar administrativo',
            'Supervisor de area',
            'Ejecutivo de ventas',
            'Desarrollador de sistemas',
        ];

        foreach ($departamentos as $nombre) {
            Departamento::updateOrCreate(
                ['nombre' => $nombre],
                ['activo' => true]
            );
        }

        foreach ($puestos as $nombre) {
            Puesto::updateOrCreate(
                ['nombre' => $nombre],
                ['activo' => true]
            );
        }

        $departamentosPorNombre = Departamento::query()->pluck('id', 'nombre');
        $puestosPorNombre = Puesto::query()->pluck('id', 'nombre');

        $empleados = [
            ['num' => 'EMP-001', 'nombre' => 'Ana', 'ap' => 'Lopez', 'am' => 'Ruiz', 'curp' => 'LORA900101MDFPRN01', 'rfc' => 'LORA900101ABC', 'nss' => 'NSS9001010001', 'depto' => 'Finanzas', 'puesto' => 'Analista de nomina', 'sal' => 500, 'tipo_pago' => 'QUINCENAL'],
            ['num' => 'EMP-002', 'nombre' => 'Carlos', 'ap' => 'Mendez', 'am' => 'Soto', 'curp' => 'MESC910202HDFNTR02', 'rfc' => 'MESC910202DEF', 'nss' => 'NSS9102020002', 'depto' => 'Operaciones', 'puesto' => 'Supervisor de area', 'sal' => 620, 'tipo_pago' => 'SEMANAL'],
            ['num' => 'EMP-003', 'nombre' => 'Luisa', 'ap' => 'Gomez', 'am' => 'Diaz', 'curp' => 'GODL920303MDFMZS03', 'rfc' => 'GODL920303GHI', 'nss' => 'NSS9203030003', 'depto' => 'Recursos Humanos', 'puesto' => 'Auxiliar administrativo', 'sal' => 420, 'tipo_pago' => 'MENSUAL'],
            ['num' => 'EMP-004', 'nombre' => 'Jorge', 'ap' => 'Hernandez', 'am' => 'Paz', 'curp' => 'HEPJ930404HDFRZG04', 'rfc' => 'HEPJ930404JKL', 'nss' => 'NSS9304040004', 'depto' => 'Ventas', 'puesto' => 'Ejecutivo de ventas', 'sal' => 460, 'tipo_pago' => 'QUINCENAL'],
            ['num' => 'EMP-005', 'nombre' => 'Mariana', 'ap' => 'Ortiz', 'am' => 'Nava', 'curp' => 'OENM940505MDFRVR05', 'rfc' => 'OENM940505MNO', 'nss' => 'NSS9405050005', 'depto' => 'Tecnologia', 'puesto' => 'Desarrollador de sistemas', 'sal' => 700, 'tipo_pago' => 'MENSUAL'],
            ['num' => 'EMP-006', 'nombre' => 'Raul', 'ap' => 'Vega', 'am' => 'Ibarra', 'curp' => 'VEIR950606HDFBGS06', 'rfc' => 'VEIR950606PQR', 'nss' => 'NSS9506060006', 'depto' => 'Operaciones', 'puesto' => 'Supervisor de area', 'sal' => 580, 'tipo_pago' => 'SEMANAL'],
            ['num' => 'EMP-007', 'nombre' => 'Diana', 'ap' => 'Sanchez', 'am' => 'Mora', 'curp' => 'SAMD960707MDFNRT07', 'rfc' => 'SAMD960707STU', 'nss' => 'NSS9607070007', 'depto' => 'Recursos Humanos', 'puesto' => 'Auxiliar administrativo', 'sal' => 430, 'tipo_pago' => 'QUINCENAL'],
            ['num' => 'EMP-008', 'nombre' => 'Pedro', 'ap' => 'Castro', 'am' => 'Luna', 'curp' => 'CALP970808HDFRST08', 'rfc' => 'CALP970808VWX', 'nss' => 'NSS9708080008', 'depto' => 'Finanzas', 'puesto' => 'Analista de nomina', 'sal' => 520, 'tipo_pago' => 'SEMANAL'],
            ['num' => 'EMP-009', 'nombre' => 'Elena', 'ap' => 'Pineda', 'am' => 'Flores', 'curp' => 'PIFE980909MDFNLS09', 'rfc' => 'PIFE980909YZA', 'nss' => 'NSS9809090009', 'depto' => 'Ventas', 'puesto' => 'Ejecutivo de ventas', 'sal' => 455, 'tipo_pago' => 'QUINCENAL'],
            ['num' => 'EMP-010', 'nombre' => 'Miguel', 'ap' => 'Ramos', 'am' => 'Torres', 'curp' => 'RATM991010HDFMNG10', 'rfc' => 'RATM991010BCD', 'nss' => 'NSS9910100010', 'depto' => 'Tecnologia', 'puesto' => 'Desarrollador de sistemas', 'sal' => 720, 'tipo_pago' => 'MENSUAL'],
        ];

        foreach ($empleados as $indice => $item) {
            Empleado::updateOrCreate(
                ['num_empleado' => $item['num']],
                [
                    'nombre' => $item['nombre'],
                    'ap_paterno' => $item['ap'],
                    'ap_materno' => $item['am'],
                    'curp' => $item['curp'],
                    'rfc' => $item['rfc'],
                    'nss' => $item['nss'],
                    'correo' => strtolower($item['nombre']) . '.' . strtolower($item['ap']) . '@empresa.local',
                    'telefono' => '5510000' . str_pad((string) ($indice + 1), 3, '0', STR_PAD_LEFT),
                    'f_ingreso' => now()->subMonths(18 - $indice)->toDateString(),
                    'f_baja' => null,
                    'tipo_cont' => 'INDETERMINADO',
                    'jornada' => 'DIURNA',
                    'tipo_pago' => $item['tipo_pago'],
                    'sal_dia' => $item['sal'],
                    'sal_int' => round($item['sal'] * 1.045, 2),
                    'depto_id' => $departamentosPorNombre[$item['depto']],
                    'puesto_id' => $puestosPorNombre[$item['puesto']],
                    'porcentaje_infonavit' => 2.5,
                    'porcentaje_afore' => 1.125,
                    'usa_fondo_ahorro' => true,
                    'porcentaje_fondo_ahorro' => 5,
                    'semanas_cotizadas' => 90 + ($indice * 2),
                    'fondo_retiro_acumulado' => 12000 + ($indice * 850),
                    'estatus' => 'ACTIVO',
                ]
            );
        }
    }
}
