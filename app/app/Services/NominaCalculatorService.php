<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\PeriodoNomina;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NominaCalculatorService
{
    private const TASA_IMSS_OBRERO = 0.02375;

    private const ISR_TRAMOS = [
        ['limite' => 8952.49, 'cuota' => 0.00, 'tasa' => 0.0192],
        ['limite' => 75984.55, 'cuota' => 171.88, 'tasa' => 0.0640],
        ['limite' => 133536.07, 'cuota' => 4461.94, 'tasa' => 0.1088],
        ['limite' => 155229.80, 'cuota' => 10723.55, 'tasa' => 0.16],
        ['limite' => 185852.57, 'cuota' => 14194.54, 'tasa' => 0.1792],
        ['limite' => 374837.88, 'cuota' => 19682.13, 'tasa' => 0.2136],
        ['limite' => 590795.99, 'cuota' => 60049.40, 'tasa' => 0.2352],
        ['limite' => 1127926.84, 'cuota' => 110842.74, 'tasa' => 0.30],
        ['limite' => 1503902.46, 'cuota' => 272613.97, 'tasa' => 0.32],
        ['limite' => 4511707.37, 'cuota' => 392841.96, 'tasa' => 0.34],
        ['limite' => PHP_FLOAT_MAX, 'cuota' => 1414947.85, 'tasa' => 0.35],
    ];

    private const TIPOS_PERCEPCION = ['HORA_EXTRA', 'BONO', 'VACACIONES_PAGADAS', 'OTRO'];

    private const TIPOS_DEDUCCION = ['FALTA', 'RETARDO', 'INCAPACIDAD', 'VACACIONES'];

    private const TIPOS_AUTOCALC_DIA_PERCEPCION = ['VACACIONES_PAGADAS'];

    private const TIPOS_AUTOCALC_DIA_DEDUCCION = ['FALTA', 'VACACIONES'];

    private const TIPOS_CANTIDAD_IMPORTE_DIRECTO = ['BONO', 'HORA_EXTRA', 'RETARDO', 'INCAPACIDAD', 'OTRO'];

    public function calcular(Empleado $empleado, PeriodoNomina $periodo, Collection $incidencias): array
    {
        [$percepcionesIncidencias, $deduccionesIncidencias] = $this->resumirIncidencias($incidencias, $empleado);

        return $this->calcularConMontosIncidencia(
            $empleado,
            $periodo,
            $percepcionesIncidencias,
            $deduccionesIncidencias
        );
    }

    /**
     * Ruta rapida para procesamiento masivo cuando ya existen montos preagregados.
     */
    public function calcularConMontosIncidencia(
        Empleado $empleado,
        PeriodoNomina $periodo,
        float $percepcionesIncidencias,
        float $deduccionesIncidencias
    ): array
    {
        $diasPagados = (float) Carbon::parse($periodo->fecha_inicio)
            ->diffInDays(Carbon::parse($periodo->fecha_fin)) + 1;

        $salarioDiario = (float) ($empleado->sal_dia ?? 0);
        $salarioIntegradoDiario = (float) ($empleado->sal_int ?? $salarioDiario);

        $sueldoBase = round($diasPagados * $salarioDiario, 2);
        $baseCotizacion = round($diasPagados * $salarioIntegradoDiario, 2);

        // Base gravable para ISR: percepciones gravables menos descuentos por incidencias.
        $baseGravable = max(0, $sueldoBase + $percepcionesIncidencias - $deduccionesIncidencias);

        $isr = $this->calcularIsrPeriodico($baseGravable, (string) $periodo->tipo_periodo);
        $imss = round($baseCotizacion * self::TASA_IMSS_OBRERO, 2);

        $porcentajeInfonavit = ((float) ($empleado->porcentaje_infonavit ?? 0)) / 100;
        $infonavit = round($baseCotizacion * $porcentajeInfonavit, 2);

        $porcentajeAfore = ((float) ($empleado->porcentaje_afore ?? 1.125)) / 100;
        $afore = round($baseCotizacion * $porcentajeAfore, 2);

        $fondoAhorro = 0.0;
        if ($empleado->usa_fondo_ahorro) {
            $porcentajeFondo = ((float) ($empleado->porcentaje_fondo_ahorro ?? 0)) / 100;
            $fondoAhorro = round($sueldoBase * $porcentajeFondo, 2);
        }

        $totalPercepciones = round($sueldoBase + $percepcionesIncidencias, 2);
        $totalDeducciones = round($deduccionesIncidencias + $isr + $imss + $infonavit + $afore + $fondoAhorro, 2);
        $netoPagado = round(max(0, $totalPercepciones - $totalDeducciones), 2);

        return [
            'dias_pagados' => round($diasPagados, 2),
            'total_percepciones' => $totalPercepciones,
            'total_deducciones' => $totalDeducciones,
            'neto_pagado' => $netoPagado,
            'detalles' => [
                ['clave' => 'P001', 'nombre' => 'SUELDO BASE', 'tipo' => 'PERCEPCION', 'importe' => $sueldoBase],
                ['clave' => 'P010', 'nombre' => 'PERCEPCIONES POR INCIDENCIAS', 'tipo' => 'PERCEPCION', 'importe' => round($percepcionesIncidencias, 2)],
                ['clave' => 'D090', 'nombre' => 'DESCUENTOS POR INCIDENCIAS', 'tipo' => 'DEDUCCION', 'importe' => round($deduccionesIncidencias, 2)],
                ['clave' => 'D001', 'nombre' => 'ISR', 'tipo' => 'DEDUCCION', 'importe' => $isr],
                ['clave' => 'D002', 'nombre' => 'IMSS OBRERO', 'tipo' => 'DEDUCCION', 'importe' => $imss],
                ['clave' => 'D003', 'nombre' => 'INFONAVIT', 'tipo' => 'DEDUCCION', 'importe' => $infonavit],
                ['clave' => 'D004', 'nombre' => 'AFORE', 'tipo' => 'DEDUCCION', 'importe' => $afore],
                ['clave' => 'D005', 'nombre' => 'FONDO DE AHORRO', 'tipo' => 'DEDUCCION', 'importe' => $fondoAhorro],
            ],
        ];
    }

    private function calcularIsrPeriodico(float $baseGravable, string $tipoPeriodo): float
    {
        $factorAnualizacion = match ($tipoPeriodo) {
            'SEMANAL' => 52,
            'CATORCENAL' => 26,
            'QUINCENAL' => 24,
            default => 12,
        };

        $ingresoAnual = $baseGravable * $factorAnualizacion;

        $limiteInferior = 0.0;
        foreach (self::ISR_TRAMOS as $tramo) {
            if ($ingresoAnual <= $tramo['limite']) {
                $excedente = max(0, $ingresoAnual - $limiteInferior);
                $isrAnual = (float) $tramo['cuota'] + ($excedente * (float) $tramo['tasa']);

                return round($isrAnual / $factorAnualizacion, 2);
            }

            $limiteInferior = (float) $tramo['limite'];
        }

        return 0.0;
    }

    /**
     * Recorre incidencias una sola vez para mejorar rendimiento en volumen.
     *
     * @return array{0: float, 1: float}
     */
    private function resumirIncidencias(Collection $incidencias, Empleado $empleado): array
    {
        $percepcionesIncidencias = 0.0;
        $deduccionesIncidencias = 0.0;
        $salarioDiario = (float) ($empleado->sal_dia ?? 0);

        foreach ($incidencias as $incidencia) {
            $tipo = (string) ($incidencia->tipo ?? '');
            if ($tipo === 'DESCANSO') {
                // El descanso se registra para control de dias sin afectar monto de nomina.
                continue;
            }

            $monto = $this->resolverMontoIncidencia($incidencia, $salarioDiario);
            if ($monto <= 0) {
                continue;
            }

            if (in_array($tipo, self::TIPOS_PERCEPCION, true)) {
                $percepcionesIncidencias += $monto;
                continue;
            }

            if (in_array($tipo, self::TIPOS_DEDUCCION, true)) {
                $deduccionesIncidencias += $monto;
            }
        }

        return [round($percepcionesIncidencias, 2), round($deduccionesIncidencias, 2)];
    }

    private function resolverMontoIncidencia(object $incidencia, float $salarioDiario): float
    {
        $tipo = (string) ($incidencia->tipo ?? '');
        $cantidad = (float) ($incidencia->cantidad ?? 0);

        if ($tipo === 'FALTA' && $cantidad > 0 && $salarioDiario > 0) {
            return round($cantidad * $salarioDiario, 2);
        }

        $montoCapturado = (float) ($incidencia->monto ?? 0);
        if ($montoCapturado > 0) {
            return $montoCapturado;
        }

        if ($cantidad <= 0 || $salarioDiario <= 0) {
            return 0.0;
        }

        if (in_array($tipo, self::TIPOS_AUTOCALC_DIA_PERCEPCION, true) || in_array($tipo, self::TIPOS_AUTOCALC_DIA_DEDUCCION, true)) {
            return round($cantidad * $salarioDiario, 2);
        }

        if (in_array($tipo, self::TIPOS_CANTIDAD_IMPORTE_DIRECTO, true)) {
            return round($cantidad, 2);
        }

        return 0.0;
    }
}
