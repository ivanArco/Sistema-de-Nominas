<?php

namespace App\Services;

use App\Models\PeriodoNomina;
use Carbon\Carbon;

class PeriodoNominaGeneratorService
{
    /**
     * @param array<int, string> $tiposPago
     * @return array{creados:int, existentes:int}
     */
    public function generarParaMes(int $anio, int $mes, array $tiposPago = []): array
    {
        $tipos = $this->resolverTiposPeriodo($tiposPago);

        $creados = 0;
        $existentes = 0;

        if (in_array('SEMANAL', $tipos, true)) {
            [$nuevos, $yaExistian] = $this->generarSemanales($anio, $mes);
            $creados += $nuevos;
            $existentes += $yaExistian;
        }

        if (in_array('QUINCENAL', $tipos, true)) {
            [$nuevos, $yaExistian] = $this->generarQuincenales($anio, $mes);
            $creados += $nuevos;
            $existentes += $yaExistian;
        }

        if (in_array('MENSUAL', $tipos, true)) {
            [$nuevos, $yaExistian] = $this->generarMensual($anio, $mes);
            $creados += $nuevos;
            $existentes += $yaExistian;
        }

        return [
            'creados' => $creados,
            'existentes' => $existentes,
        ];
    }

    /**
     * @param array<int, string> $tiposPago
     * @return array<int, string>
     */
    private function resolverTiposPeriodo(array $tiposPago): array
    {
        if ($tiposPago === []) {
            return ['SEMANAL', 'QUINCENAL', 'MENSUAL'];
        }

        $resultado = [];
        foreach ($tiposPago as $tipoPago) {
            $tipo = strtoupper(trim((string) $tipoPago));
            if ($tipo === 'SEMANAL') {
                $resultado[] = 'SEMANAL';
                continue;
            }

            if (in_array($tipo, ['QUINCENAL', 'CATORCENAL'], true)) {
                $resultado[] = 'QUINCENAL';
                continue;
            }

            if ($tipo === 'MENSUAL') {
                $resultado[] = 'MENSUAL';
            }
        }

        return array_values(array_unique($resultado));
    }

    /**
     * @return array{0:int, 1:int}
     */
    private function generarSemanales(int $anio, int $mes): array
    {
        $creados = 0;
        $existentes = 0;

        $inicioMes = Carbon::create($anio, $mes, 1)->startOfDay();
        $finMes = $inicioMes->copy()->endOfMonth();

        for ($semana = 1; $semana <= 5; $semana++) {
            $inicio = $inicioMes->copy()->addDays(($semana - 1) * 7);
            if ($inicio->gt($finMes)) {
                break;
            }

            $fin = $inicio->copy()->addDays(6);
            if ($fin->gt($finMes)) {
                $fin = $finMes->copy();
            }

            $resultado = $this->crearPeriodoSeguro(
                $anio,
                'SEMANAL',
                (int) $inicio->isoWeek(),
                $inicio,
                $fin,
                $fin
            );

            if ($resultado) {
                $creados++;
            } else {
                $existentes++;
            }
        }

        return [$creados, $existentes];
    }

    /**
     * @return array{0:int, 1:int}
     */
    private function generarQuincenales(int $anio, int $mes): array
    {
        $creados = 0;
        $existentes = 0;

        $inicioMes = Carbon::create($anio, $mes, 1)->startOfDay();
        $finMes = $inicioMes->copy()->endOfMonth();

        $rangos = [
            [$inicioMes->copy(), $inicioMes->copy()->day(15)],
            [$inicioMes->copy()->day(16), $finMes->copy()],
        ];

        foreach ($rangos as [$inicio, $fin]) {
            $numeroPeriodo = (($mes - 1) * 2) + ((int) $inicio->day <= 15 ? 1 : 2);

            $resultado = $this->crearPeriodoSeguro(
                $anio,
                'QUINCENAL',
                $numeroPeriodo,
                $inicio,
                $fin,
                $fin
            );

            if ($resultado) {
                $creados++;
            } else {
                $existentes++;
            }
        }

        return [$creados, $existentes];
    }

    /**
     * @return array{0:int, 1:int}
     */
    private function generarMensual(int $anio, int $mes): array
    {
        $inicio = Carbon::create($anio, $mes, 1)->startOfDay();
        $fin = $inicio->copy()->endOfMonth();

        $resultado = $this->crearPeriodoSeguro(
            $anio,
            'MENSUAL',
            $mes,
            $inicio,
            $fin,
            $fin
        );

        return $resultado ? [1, 0] : [0, 1];
    }

    private function crearPeriodoSeguro(
        int $anio,
        string $tipo,
        int $numeroSugerido,
        Carbon $inicio,
        Carbon $fin,
        Carbon $fechaPago
    ): bool {
        if ($this->periodoExiste($tipo, $inicio, $fin)) {
            return false;
        }

        $numeroPeriodo = $this->resolverNumeroPeriodoDisponible($anio, $tipo, $numeroSugerido);

        PeriodoNomina::create([
            'anio' => $anio,
            'numero_periodo' => $numeroPeriodo,
            'tipo_periodo' => $tipo,
            'fecha_inicio' => $inicio->toDateString(),
            'fecha_fin' => $fin->toDateString(),
            'fecha_pago' => $fechaPago->toDateString(),
            'estatus' => 'ABIERTO',
        ]);

        return true;
    }

    private function resolverNumeroPeriodoDisponible(int $anio, string $tipo, int $numeroSugerido): int
    {
        $numero = max(1, $numeroSugerido);

        while (PeriodoNomina::query()
            ->where('anio', $anio)
            ->where('tipo_periodo', $tipo)
            ->where('numero_periodo', $numero)
            ->exists()) {
            $numero++;
        }

        return $numero;
    }

    private function periodoExiste(string $tipo, Carbon $inicio, Carbon $fin): bool
    {
        return PeriodoNomina::query()
            ->where('tipo_periodo', $tipo)
            ->whereDate('fecha_inicio', $inicio->toDateString())
            ->whereDate('fecha_fin', $fin->toDateString())
            ->exists();
    }
}
