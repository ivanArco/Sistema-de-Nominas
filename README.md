# Sistema de Nomina Empresarial

## Resumen ejecutivo

Este proyecto es una plataforma web desarrollada en Laravel para administrar de forma centralizada procesos administrativos y operativos de nomina en una organizacion.

El sistema integra en una sola aplicacion:

- Control de acceso y usuarios por rol.
- Gestion de clientes.
- Gestion de empleados y datos laborales.
- Catalogos de nomina (periodos y conceptos).
- Registro de incidencias que impactan el pago.
- Calculo de nomina individual y masivo.
- Reportes operativos y exportaciones en PDF y CSV.

---

## Importancia del proyecto

La nomina es un proceso critico de cualquier empresa porque impacta directamente en:

- Cumplimiento legal y fiscal.
- Confianza del personal.
- Salud financiera y control administrativo.
- Trazabilidad para auditoria y toma de decisiones.

Este sistema reduce errores operativos al estandarizar reglas, automatizar calculos y mantener un historial consultable de la operacion.

---

## Objetivo general

Proveer una solucion integral para administrar y calcular nomina con controles de seguridad, consistencia de datos y capacidades de reporte para seguimiento operativo.

## Objetivos especificos

1. Centralizar informacion de usuarios, clientes y empleados.
2. Automatizar calculos de percepciones y deducciones.
3. Garantizar compatibilidad entre tipo de pago y periodos de nomina.
4. Facilitar consulta y exportacion de resultados en formatos utiles.
5. Fortalecer control interno con permisos por rol y autorizaciones sensibles.

---

## Alcance funcional

### 1. Autenticacion y seguridad

- Inicio y cierre de sesion.
- Acceso por usuario o correo.
- Validacion de usuario activo.
- Control de acceso por rol mediante middleware.

### 2. Dashboard ejecutivo

- Indicadores de usuarios, clientes y empleados activos.
- Conteo de incidencias y nominas del mes.
- Neto pagado mensual.
- Graficas de tendencia y distribucion operativa.

### 3. Modulo de usuarios

- Alta, consulta, edicion y eliminacion.
- Filtros por texto, rol, estado, activo y fechas.
- Exportacion de reporte PDF.
- Eliminacion protegida con autorizacion de supervisor o jefe de area.

### 4. Modulo de clientes

- Alta, consulta, edicion y eliminacion.
- Filtros por texto, estatus, ciudad y estado.
- Exportacion de reporte PDF.
- Eliminacion protegida con autorizacion de supervisor o jefe de area.

### 5. Modulo de empleados

- CRUD completo con validaciones de identidad laboral.
- Datos de salario, seguridad social, tipo de pago y estatus.
- Registro de historial laboral ante cambios relevantes.

### 6. Catalogos de nomina

- Gestion de periodos de pago.
- Gestion de conceptos de nomina (percepcion, deduccion, otro pago).
- Vista integrada para operacion de catalogos.

### 7. Modulo de incidencias

- Registro por empleado y periodo.
- Tipos de incidencia que afectan percepciones y deducciones.
- Resolucion automatica de montos por reglas de negocio.

### 8. Modulo de nominas

- Calculo individual por empleado y periodo.
- Generacion masiva por tipo de pago.
- Actualizacion de estatus (borrador, calculada, pagada, cancelada).
- Vista de detalle estilo recibo.

### 9. Reportes

- Centro de reportes de nomina, empleados e incidencias.
- Exportacion de nominas en PDF y CSV.
- Impresion de recibos con formato de copia empleado y patron.

---

## Reglas de negocio principales

### Periodos de pago

- Tipos: semanal, quincenal/catorcenal y mensual.
- Generacion automatica de periodos por mes.
- Unicidad por anio, numero de periodo y tipo.
- Estatus de periodo: abierto, calculado, cerrado, timbrado.

### Calculo de nomina

- Base salarial por dias del periodo.
- ISR periodico con tabla anual simplificada y prorrateo.
- IMSS obrero parametrizado.
- INFONAVIT y AFORE por porcentaje configurable por empleado.
- Fondo de ahorro opcional por empleado.

### Incidencias

- Percepciones: hora extra, bono, vacaciones pagadas, otro.
- Deducciones: falta, retardo, incapacidad, vacaciones.
- Descanso se registra para control sin afectar monto.

### Integridad y consistencia

- Una nomina por empleado y periodo.
- Regeneracion del detalle en recálculo.
- Alta automatica de conceptos tecnicos faltantes durante el calculo.
- Compatibilidad obligatoria entre tipo de pago del empleado y tipo de periodo.

---

## Arquitectura tecnica

### Stack

- Backend: Laravel 12.
- Lenguaje: PHP 8.2+.
- Base de datos: MySQL o MariaDB.
- Frontend: Blade + Vite.
- PDF: barryvdh/laravel-dompdf.

### Organizacion del proyecto

- app/app/Http/Controllers: controladores por modulo.
- app/app/Models: entidades y relaciones Eloquent.
- app/app/Services: logica de negocio especializada.
- app/database/migrations: evolucion del esquema.
- app/database/seeders: datos base para arranque.
- app/resources/views: interfaz por modulo.
- app/routes/web.php: rutas y permisos.

---

## Modelo de datos (resumen)

Entidades principales:

- users
- clientes
- departamentos
- puestos
- empleados
- empleado_historiales
- periodo_nominas
- concepto_nominas
- incidencias
- nominas
- nomina_detalles

Relaciones clave:

- Empleado pertenece a departamento y puesto.
- Incidencia pertenece a empleado y periodo.
- Nomina pertenece a empleado y periodo.
- NominaDetalle pertenece a nomina y concepto.
- EmpleadoHistorial pertenece a empleado y puesto.

---

## Roles y permisos

Roles operativos definidos:

- EMPLEADO
- VENDEDOR
- SUPERVISOR
- JEFE_AREA
- CONTADOR
- SECRETARIA

Se mantiene compatibilidad con roles legacy mediante normalizacion automatica.

---

## Fortalezas del proyecto

1. Cobertura completa del flujo de nomina en un solo sistema.
2. Separacion clara entre controladores, modelos y servicios de negocio.
3. Seguridad por autenticacion y autorizacion por rol.
4. Control de operaciones sensibles con autorizacion de supervisor.
5. Automatizacion de periodos e incidencias para reducir trabajo manual.
6. Recalculo consistente y trazable de nomina.
7. Reportes utiles para operacion y auditoria.
8. Estructura escalable para nuevas integraciones.
9. Seeders para entorno de pruebas y demostracion.

---

## Justificacion del proyecto

### 1. Justificacion funcional

Sin un sistema integrado de nomina, la operacion suele fragmentarse entre hojas de calculo, capturas manuales y multiples fuentes de datos. Esto incrementa errores de captura, duplicidad de informacion y tiempos de cierre.

Este proyecto se justifica porque centraliza en una sola plataforma los procesos criticos de usuarios, empleados, incidencias, periodos y nominas, reduciendo friccion operativa y mejorando control.

### 2. Justificacion tecnica

Se eligio Laravel por su estabilidad, mantenimiento comunitario y capacidades nativas para:

- Autenticacion y autorizacion por middleware.
- ORM Eloquent para modelado relacional claro.
- Migraciones versionadas para evolucion controlada del esquema.
- Validaciones de entrada y manejo consistente de errores.

Adicionalmente, separar logica de negocio en servicios permite que el calculo de nomina y la generacion de periodos sean mantenibles y reutilizables.

### 3. Justificacion de control interno

El sistema aplica mecanismos concretos de control:

- Restriccion de acceso por rol para reducir acciones no autorizadas.
- Eliminaciones sensibles con autorizacion de supervisor o jefe de area.
- Unicidad de nomina por empleado y periodo para evitar pagos duplicados.
- Historial de cambios laborales para trazabilidad de decisiones.

Esto fortalece la gobernanza operativa y la auditabilidad del proceso.

### 4. Justificacion de negocio

La nomina impacta directamente en costo laboral, clima organizacional y cumplimiento normativo. Una operacion lenta o inconsistente genera riesgo financiero y reputacional.

Con este sistema se obtiene:

- Mayor velocidad de procesamiento (calculo individual y masivo).
- Mayor visibilidad de resultados (dashboard y reportes).
- Mejor calidad de datos por validaciones y estructura relacional.
- Mejora en la toma de decisiones por indicadores periodicos.

### 5. Justificacion academica y de evolucion

El proyecto demuestra implementacion real de buenas practicas de ingenieria de software en un dominio administrativo complejo:

- Modelado de entidades y reglas de negocio.
- Integridad referencial y restricciones.
- Arquitectura modular y mantenible.
- Reporteria orientada a uso real.

Aunque el calculo fiscal actual es simplificado, el diseno facilita evolucionar hacia una version productiva con reglas oficiales SAT, bitacora completa y pruebas automatizadas de alto nivel.

---

## Evidencia de cumplimiento de objetivos

1. Objetivo: centralizar informacion operativa.
Resultado: modulos integrados de usuarios, clientes, empleados, incidencias y nomina en una misma aplicacion.

2. Objetivo: automatizar el calculo de nomina.
Resultado: servicio de calculo con desglose de percepciones, deducciones e impuestos parametrizados.

3. Objetivo: asegurar consistencia del proceso.
Resultado: reglas de compatibilidad tipo pago-periodo, unicidad por empleado/periodo y regeneracion de detalles.

4. Objetivo: facilitar control y analisis.
Resultado: dashboard con indicadores clave y reportes exportables PDF/CSV.

5. Objetivo: fortalecer seguridad operativa.
Resultado: control por roles y autorizacion reforzada en operaciones de eliminacion.

---

## Limitaciones actuales

1. El ISR esta implementado con enfoque simplificado academico.
2. No hay una suite robusta de pruebas automatizadas del dominio.
3. No se incluye timbrado fiscal productivo en esta version.

---

## Recomendaciones de evolucion

1. Incorporar pruebas unitarias y de integracion para calculo de nomina.
2. Integrar tablas fiscales oficiales y reglas completas vigentes.
3. Agregar bitacora de auditoria por accion critica.
4. Incorporar API para integracion con sistemas externos.
5. Implementar versionado de reglas de calculo por periodo fiscal.

---

## Flujo operativo del proceso de nomina

1. El area administrativa mantiene catalogos de periodos y conceptos.
2. El area de RH o supervision mantiene empleados y condiciones laborales.
3. Se capturan incidencias por empleado y periodo.
4. El area de nomina calcula nomina de forma individual o masiva.
5. Se revisa el detalle de percepciones y deducciones por recibo.
6. Se actualiza estatus operativo (borrador, calculada, pagada o cancelada).
7. Se generan reportes para control interno y evidencia documental.

Este flujo reduce retrabajo porque evita la recaptura entre sistemas aislados.

---

## Casos de uso clave

### Caso 1: Cierre quincenal estandar

- Se registran faltas, bonos y horas extra.
- Se ejecuta calculo masivo para personal quincenal.
- Se valida total neto a pagar y se emite reporte PDF.

### Caso 2: Revision de discrepancias

- Se consulta detalle de nomina por empleado.
- Se contrasta deduccion por incidencias y aportaciones.
- Se recalcula para regenerar detalle consistente.

### Caso 3: Auditoria interna

- Se filtran nominas por periodo y estatus.
- Se exporta evidencia en PDF/CSV.
- Se consulta historial laboral para sustentar cambios de condiciones.

---

## Indicadores sugeridos para medir exito

1. Tiempo promedio de cierre de nomina por periodo.
2. Porcentaje de nominas recalculadas por error de captura.
3. Porcentaje de incidencias registradas antes del cierre.
4. Diferencia entre nomina proyectada y pagada.
5. Porcentaje de operaciones sensibles correctamente autorizadas.
6. Tiempo de respuesta para generar reportes ejecutivos.

Mientras mas bajo sea el retrabajo y mas estable sea el cierre por periodo, mayor madurez operativa refleja la plataforma.

---

## Impacto esperado en la organizacion

### Corto plazo

- Ordenamiento de datos dispersos.
- Mayor visibilidad de la operacion actual.
- Disminucion de errores manuales recurrentes.

### Mediano plazo

- Estandarizacion del proceso de nomina entre areas.
- Mayor capacidad de planeacion financiera por historicos consistentes.
- Mejor tiempo de respuesta ante solicitudes de auditoria.

### Largo plazo

- Base lista para evolucionar a cumplimiento fiscal productivo.
- Integracion con otros sistemas administrativos.
- Gobierno de datos robusto para decisiones estrategicas.

---

## Alcance del reporte y fuentes

Este documento se elaboro con base en la revision funcional y tecnica de:

- Rutas y middleware de autorizacion.
- Controladores y servicios de negocio.
- Modelos y migraciones de base de datos.
- Vistas principales de operacion y reporteria.
- Seeders y documentacion interna del modulo de nomina.

Por ello, el contenido representa el estado real implementado del sistema en el codigo fuente, no solo una propuesta teorica.

---

## Credenciales de acceso demo

Usuario supervisor creado por seeder:

- Usuario: supervisor1
- Correo: supervisor@sistema.local
- Contrasena: Supervisor123!

---

## Rutas principales

- /login
- /dashboard
- /usuarios
- /clientes
- /empleados
- /catalogos-nomina
- /periodos-nomina
- /conceptos-nomina
- /incidencias
- /nominas
- /reportes

---

## Estado del proyecto

Proyecto funcional para gestion operativa de nomina con enfoque academico-profesional, listo para demostracion, pruebas de proceso y evolucion hacia un escenario productivo con ajustes fiscales y mayor cobertura de testing.
