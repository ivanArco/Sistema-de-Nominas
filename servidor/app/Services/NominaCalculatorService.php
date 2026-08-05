<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\PeriodoNomina;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NominaCalculatorService
{
    // Valores de respaldo usados cuando no existe configuracion explicita.
    private const UMA_DIARIA_DEFAULT = 113.14;

    // Tope legal de SBC para IMSS: 25 UMA diarias.
    private const TOPE_SBC_UMA_DEFAULT = 25;

    // Tasas obreras aproximadas por componente para calcular IMSS retenido al trabajador.
    // Se mantienen separadas para facilitar ajuste cuando cambie la normativa.
    private const IMSS_COMPONENTES_OBRERO_DEFAULT = [
        'prestaciones_dinero' => 0.0025,
        'gastos_medicos_pensionados' => 0.00375,
        'invalidez_vida' => 0.00625,
        'cesantia_vejez' => 0.01125,
        'excedente_3_uma' => 0.0040,
    ];

    // Tarifa ISR anual de referencia para el calculo periodico por anualizacion.
    private const ISR_TRAMOS_ANUALES_DEFAULT = [
        ['inferior' => 0.01, 'superior' => 8952.49, 'cuota' => 0.00, 'tasa' => 0.0192],
        ['inferior' => 8952.50, 'superior' => 75984.55, 'cuota' => 171.88, 'tasa' => 0.0640],
        ['inferior' => 75984.56, 'superior' => 133536.07, 'cuota' => 4461.94, 'tasa' => 0.1088],
        ['inferior' => 133536.08, 'superior' => 155229.80, 'cuota' => 10723.55, 'tasa' => 0.1600],
        ['inferior' => 155229.81, 'superior' => 185852.57, 'cuota' => 14194.54, 'tasa' => 0.1792],
        ['inferior' => 185852.58, 'superior' => 374837.88, 'cuota' => 19682.13, 'tasa' => 0.2136],
        ['inferior' => 374837.89, 'superior' => 590795.99, 'cuota' => 60049.40, 'tasa' => 0.2352],
        ['inferior' => 590796.00, 'superior' => 1127926.84, 'cuota' => 110842.74, 'tasa' => 0.3000],
        ['inferior' => 1127926.85, 'superior' => 1503902.46, 'cuota' => 272613.97, 'tasa' => 0.3200],
        ['inferior' => 1503902.47, 'superior' => 4511707.37, 'cuota' => 392841.96, 'tasa' => 0.3400],
        ['inferior' => 4511707.38, 'superior' => PHP_FLOAT_MAX, 'cuota' => 1414947.85, 'tasa' => 0.3500],
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
        $sbcDiarioTopado = $this->aplicarTopeSbcDiario($salarioIntegradoDiario);

        $sueldoBase = round($diasPagados * $salarioDiario, 2);
        $baseCotizacion = round($diasPagados * $sbcDiarioTopado, 2);

        // Base gravable para ISR: percepciones gravables menos descuentos por incidencias.
        $baseGravable = max(0, $sueldoBase + $percepcionesIncidencias - $deduccionesIncidencias);

        $isr = $this->calcularIsrPeriodico($baseGravable, (string) $periodo->tipo_periodo);
        $imss = $this->calcularImssObrero($sbcDiarioTopado, $diasPagados);

        $porcentajeInfonavit = $this->normalizarPorcentaje((float) ($empleado->porcentaje_infonavit ?? 0));
        $infonavit = round($baseCotizacion * $porcentajeInfonavit, 2);

        $porcentajeAfore = $this->normalizarPorcentaje((float) ($empleado->porcentaje_afore ?? 1.125));
        $afore = round($baseCotizacion * $porcentajeAfore, 2);

        $fondoAhorro = 0.0;
        if ($empleado->usa_fondo_ahorro) {
            $porcentajeFondo = $this->normalizarPorcentaje((float) ($empleado->porcentaje_fondo_ahorro ?? 0));
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
        if ($baseGravable <= 0) {
            return 0.0;
        }

        $factorAnualizacion = match ($tipoPeriodo) {
            'SEMANAL' => 52,
            'CATORCENAL' => 26,
            'QUINCENAL' => 24,
            default => 12,
        };

        $ingresoAnual = $baseGravable * $factorAnualizacion;

        foreach ($this->obtenerTramosIsrAnuales() as $tramo) {
            if ($ingresoAnual >= (float) $tramo['inferior'] && $ingresoAnual <= (float) $tramo['superior']) {
                $excedente = max(0, $ingresoAnual - (float) $tramo['inferior']);
                $isrAnual = (float) $tramo['cuota'] + ($excedente * (float) $tramo['tasa']);

                return round($isrAnual / $factorAnualizacion, 2);
            }
        }

        return 0.0;
    }

    private function aplicarTopeSbcDiario(float $sbcDiario): float
    {
        $tope = $this->obtenerUmaDiaria() * $this->obtenerTopeSbcUma();

        return round(max(0.0, min($sbcDiario, $tope)), 2);
    }

    private function calcularImssObrero(float $sbcDiarioTopado, float $diasPagados): float
    {
        if ($sbcDiarioTopado <= 0 || $diasPagados <= 0) {
            return 0.0;
        }

        $baseCotizacion = $sbcDiarioTopado * $diasPagados;
        $uma = $this->obtenerUmaDiaria();
        $excedenteDiario = max(0.0, $sbcDiarioTopado - ($uma * 3));
        $baseExcedente = $excedenteDiario * $diasPagados;
        $componentes = $this->obtenerComponentesImssObrero();

        $imss = 0.0;
        $imss += $baseCotizacion * ((float) ($componentes['prestaciones_dinero'] ?? 0));
        $imss += $baseCotizacion * ((float) ($componentes['gastos_medicos_pensionados'] ?? 0));
        $imss += $baseCotizacion * ((float) ($componentes['invalidez_vida'] ?? 0));
        $imss += $baseCotizacion * ((float) ($componentes['cesantia_vejez'] ?? 0));
        $imss += $baseExcedente * ((float) ($componentes['excedente_3_uma'] ?? 0));

        return round($imss, 2);
    }

    private function normalizarPorcentaje(float $porcentaje): float
    {
        $valor = max(0.0, min($porcentaje, $this->obtenerPorcentajeMaximoDeduccion()));

        return $valor / 100;
    }

    /**
     * @return array<int, array{inferior: float|int, superior: float|int, cuota: float|int, tasa: float|int}>
     */
    private function obtenerTramosIsrAnuales(): array
    {
        $tramos = config('nomina.isr_tramos_anuales', self::ISR_TRAMOS_ANUALES_DEFAULT);

        return is_array($tramos) ? $tramos : self::ISR_TRAMOS_ANUALES_DEFAULT;
    }

    private function obtenerUmaDiaria(): float
    {
        return (float) config('nomina.uma_diaria', self::UMA_DIARIA_DEFAULT);
    }

    private function obtenerTopeSbcUma(): float
    {
        return (float) config('nomina.tope_sbc_uma', self::TOPE_SBC_UMA_DEFAULT);
    }

    /**
     * @return array<string, float|int>
     */
    private function obtenerComponentesImssObrero(): array
    {
        $componentes = config('nomina.imss_componentes_obrero', self::IMSS_COMPONENTES_OBRERO_DEFAULT);

        return is_array($componentes) ? $componentes : self::IMSS_COMPONENTES_OBRERO_DEFAULT;
    }

    private function obtenerPorcentajeMaximoDeduccion(): float
    {
        return (float) config('nomina.porcentaje_maximo_deduccion', 30.0);
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
