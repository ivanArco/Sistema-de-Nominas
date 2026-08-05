<?php

return [
    // Cuando esta activo, sal_int se recalcula automaticamente al guardar Empleado.
    'calcular_salario_integrado_automatico' => true,

    // Parametros para factor de integracion del salario diario.
    'aguinaldo_dias' => 15.0,
    'prima_vacacional' => 0.25,
    'factor_integracion_minimo' => 1.0,

    // Ajustar anualmente con base en los valores oficiales vigentes.
    'uma_diaria' => 113.14,

    // Tope del salario base de cotizacion para IMSS en UMA diarias.
    'tope_sbc_uma' => 25,

    // Porcentaje maximo permitido para deducciones parametrizadas por empleado.
    'porcentaje_maximo_deduccion' => 30.0,

    // Tasas obreras por componente para retencion IMSS del trabajador.
    'imss_componentes_obrero' => [
        'prestaciones_dinero' => 0.0025,
        'gastos_medicos_pensionados' => 0.00375,
        'invalidez_vida' => 0.00625,
        'cesantia_vejez' => 0.01125,
        'excedente_3_uma' => 0.0040,
    ],

    // Tarifa ISR anual de referencia usada para calcular ISR por periodo mediante anualizacion.
    'isr_tramos_anuales' => [
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
    ],
];
