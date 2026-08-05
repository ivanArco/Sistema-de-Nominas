<?php

namespace Tests\Unit;

use App\Models\Empleado;
use App\Models\PeriodoNomina;
use App\Services\NominaCalculatorService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class NominaCalculatorServiceTest extends TestCase
{
    public function test_aplica_tope_sbc_e_imss_con_componentes(): void
    {
        $service = new NominaCalculatorService();

        $empleado = new Empleado([
            'sal_dia' => 1200,
            'sal_int' => 5000,
            'porcentaje_infonavit' => 2.5,
            'porcentaje_afore' => 1.125,
            'usa_fondo_ahorro' => true,
            'porcentaje_fondo_ahorro' => 5,
        ]);

        $periodo = new PeriodoNomina([
            'tipo_periodo' => 'MENSUAL',
            'fecha_inicio' => '2026-08-01',
            'fecha_fin' => '2026-08-30',
        ]);

        $resultado = $service->calcularConMontosIncidencia($empleado, $periodo, 0.0, 0.0);

        $imss = $this->detalleImporte($resultado['detalles'], 'D002');
        $infonavit = $this->detalleImporte($resultado['detalles'], 'D003');
        $afore = $this->detalleImporte($resultado['detalles'], 'D004');

        $this->assertSame(2314.00, $imss);
        $this->assertSame(2121.38, $infonavit);
        $this->assertSame(954.62, $afore);
    }

    public function test_total_deducciones_coincide_con_desglose(): void
    {
        $service = new NominaCalculatorService();

        $empleado = new Empleado([
            'sal_dia' => 500,
            'sal_int' => 522.50,
            'porcentaje_infonavit' => 2.5,
            'porcentaje_afore' => 1.125,
            'usa_fondo_ahorro' => true,
            'porcentaje_fondo_ahorro' => 5,
        ]);

        $periodo = new PeriodoNomina([
            'tipo_periodo' => 'QUINCENAL',
            'fecha_inicio' => '2026-08-01',
            'fecha_fin' => '2026-08-15',
        ]);

        $incidencias = new Collection([
            (object) ['tipo' => 'BONO', 'cantidad' => 0, 'monto' => 250],
            (object) ['tipo' => 'FALTA', 'cantidad' => 1, 'monto' => 0],
        ]);

        $resultado = $service->calcular($empleado, $periodo, $incidencias);

        $sumaDeducciones = array_reduce(
            $resultado['detalles'],
            static fn (float $acumulado, array $detalle): float => $detalle['tipo'] === 'DEDUCCION'
                ? $acumulado + (float) $detalle['importe']
                : $acumulado,
            0.0
        );

        $this->assertSame(round($sumaDeducciones, 2), (float) $resultado['total_deducciones']);
        $this->assertSame(
            round((float) $resultado['total_percepciones'] - (float) $resultado['total_deducciones'], 2),
            (float) $resultado['neto_pagado']
        );
    }

    /**
     * @param array<int, array<string, mixed>> $detalles
     */
    private function detalleImporte(array $detalles, string $clave): float
    {
        foreach ($detalles as $detalle) {
            if (($detalle['clave'] ?? null) === $clave) {
                return (float) ($detalle['importe'] ?? 0);
            }
        }

        return 0.0;
    }
}
