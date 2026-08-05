<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class SalarioIntegradoService
{
    public static function calcular(float $salarioDiario, null|string|CarbonInterface $fechaIngreso, ?CarbonInterface $fechaReferencia = null): float
    {
        if ($salarioDiario <= 0) {
            return 0.0;
        }

        $antiguedad = self::calcularAntiguedad($fechaIngreso, $fechaReferencia);
        $anioLaboral = $antiguedad + 1;

        $diasVacaciones = self::diasVacacionesPorAnioLaboral($anioLaboral);
        $primaVacacional = (float) config('nomina.prima_vacacional', 0.25);
        $aguinaldoDias = (float) config('nomina.aguinaldo_dias', 15.0);
        $factorMinimo = (float) config('nomina.factor_integracion_minimo', 1.0);

        $factorIntegracion = (365.0 + $aguinaldoDias + ($diasVacaciones * $primaVacacional)) / 365.0;
        $factorIntegracion = max($factorIntegracion, $factorMinimo);

        return round($salarioDiario * $factorIntegracion, 2);
    }

    public static function calcularAntiguedad(null|string|CarbonInterface $fechaIngreso, ?CarbonInterface $fechaReferencia = null): int
    {
        if ($fechaIngreso === null || $fechaIngreso === '') {
            return 0;
        }

        $ingreso = $fechaIngreso instanceof CarbonInterface ? $fechaIngreso->copy() : Carbon::parse($fechaIngreso);
        $referencia = $fechaReferencia?->copy() ?? Carbon::now();

        if ($ingreso->greaterThan($referencia)) {
            return 0;
        }

        return $ingreso->diffInYears($referencia);
    }

    private static function diasVacacionesPorAnioLaboral(int $anioLaboral): int
    {
        $anio = max(1, $anioLaboral);

        if ($anio <= 1) {
            return 12;
        }

        if ($anio <= 5) {
            return 12 + (($anio - 1) * 2);
        }

        $bloque = intdiv($anio - 6, 5);

        return 22 + ($bloque * 2);
    }
}
