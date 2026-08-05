<?php

namespace Tests\Unit;

use App\Services\SalarioIntegradoService;
use Carbon\Carbon;
use Tests\TestCase;

class SalarioIntegradoServiceTest extends TestCase
{
    public function test_calcula_salario_integrado_con_antiguedad_base(): void
    {
        config()->set('nomina.aguinaldo_dias', 15.0);
        config()->set('nomina.prima_vacacional', 0.25);

        $resultado = SalarioIntegradoService::calcular(
            500.0,
            '2026-01-10',
            Carbon::parse('2026-08-05')
        );

        $this->assertSame(524.66, $resultado);
    }

    public function test_incrementa_factor_por_antiguedad_mayor(): void
    {
        config()->set('nomina.aguinaldo_dias', 15.0);
        config()->set('nomina.prima_vacacional', 0.25);

        $resultado = SalarioIntegradoService::calcular(
            500.0,
            '2012-01-10',
            Carbon::parse('2026-08-05')
        );

        $this->assertSame(528.77, $resultado);
    }
}
