# Entrega 2 - Modulo de Nominas

## Alcance funcional implementado

Se habilito la gestion completa del modulo de nominas con operaciones CRUD para:

- Empleados
- Periodos de nomina
- Conceptos de nomina
- Incidencias
- Nominas (calculo y consulta de detalle)

## Reglas de negocio aplicadas

1. Periodos de pago
- Tipos soportados: SEMANAL, CATORCENAL, QUINCENAL y MENSUAL.
- Unicidad por periodo: (anio, numero_periodo, tipo_periodo).
- Estados de periodo: ABIERTO, CALCULADO, CERRADO, TIMBRADO.

2. Deducciones y prestaciones en calculo de nomina
- ISR: calculo por tarifa anual simplificada y prorrateo al periodo.
- IMSS obrero: porcentaje sobre base gravable (2.375% parametrizado en servicio).
- INFONAVIT: porcentaje configurable por empleado.
- AFORE: porcentaje configurable por empleado (default 1.125%).
- Fondo de ahorro: opcional por empleado, con porcentaje configurable.

3. Incidencias
- Tipos con efecto en percepciones: HORA_EXTRA, BONO, VACACIONES, OTRO.
- Tipos con efecto en deducciones: FALTA, RETARDO, INCAPACIDAD.
- Las incidencias se aplican por empleado y periodo.

4. Integridad y trazabilidad
- Una nomina por empleado y periodo.
- El detalle de nomina se regenera en cada recálculo.
- Los conceptos tecnicos (ISR, IMSS, etc.) se registran automaticamente si no existen.

## Acciones sugeridas de base de datos

1. Migraciones
- Ejecutar migraciones pendientes para aplicar campos de seguridad social:
  - porcentaje_infonavit
  - porcentaje_afore
  - usa_fondo_ahorro
  - porcentaje_fondo_ahorro

2. Catalogos iniciales
- Cargar departamentos y puestos activos.
- Registrar periodos de nomina del anio vigente.
- Mantener conceptos de percepcion/deduccion institucionales.

3. Operacion recomendada
- Alta de empleados con datos fiscales y de seguridad social (RFC, CURP, NSS).
- Captura de incidencias por periodo antes de calcular nomina.
- Calculo de nomina por empleado/periodo y cambio de estatus hasta PAGADA.

## Nota academica

El calculo de ISR se implemento con una tarifa simplificada para fines academicos de la actividad. Para ambiente productivo se recomienda usar tablas oficiales vigentes SAT y reglas completas de subsidio y exenciones.
