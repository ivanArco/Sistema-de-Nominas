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

- servidor/app/Http/Controllers: controladores por modulo.
- servidor/app/Models: entidades y relaciones Eloquent.
- servidor/app/Services: logica de negocio especializada.
- servidor/database/migrations: evolucion del esquema.
- servidor/database/seeders: datos base para arranque.
- servidor/resources/views: interfaz por modulo.
- servidor/routes/web.php: rutas y permisos.
- interfaz/: recursos de la capa de presentacion.
- base_de_datos/: esquema y respaldos de datos.
- documentacion/: manuales y referencias del proyecto.

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

### Matriz de permisos por tipo de usuario

#### EMPLEADO

Es el usuario con menos permisos.

Puede:

- Ver su perfil.
- Actualizar datos personales (telefono, direccion, correo).
- Consultar su historial de nominas.
- Descargar recibos de nomina en PDF.
- Consultar vacaciones disponibles.
- Solicitar permisos o vacaciones.
- Cambiar su contrasena.

No puede:

- Ver informacion de otros empleados.
- Modificar nominas.
- Registrar asistencias.

#### VENDEDOR

Ademas de todo lo que hace un empleado:

Puede:

- Registrar ventas.
- Consultar sus ventas.
- Ver comisiones.
- Ver bonos por ventas.
- Consultar metas de ventas.

#### SECRETARIA

Puede:

- Registrar empleados.
- Editar informacion de empleados.
- Gestionar expedientes.
- Registrar documentos.
- Registrar incapacidades.
- Registrar permisos.
- Gestionar contratos.
- Consultar reportes administrativos.

No puede:

- Calcular nomina.
- Modificar salarios.

#### SUPERVISOR

Se encarga del personal de su area.

Puede:

- Ver los empleados bajo su supervision.
- Registrar asistencias.
- Registrar retardos.
- Registrar faltas.
- Registrar horas extra.
- Aprobar permisos.
- Aprobar vacaciones.
- Consultar reportes de asistencia.

#### JEFE_AREA

Tiene permisos mas amplios que un supervisor.

Puede:

- Todo lo del Supervisor.
- Aprobar definitivamente vacaciones.
- Autorizar horas extra.
- Autorizar bonos.
- Consultar reportes del departamento.
- Evaluar desempeno del personal.

#### CONTADOR

Puede:

- Calcular nomina.
- Registrar percepciones.
- Registrar deducciones.
- Calcular ISR.
- Calcular IMSS (si aplica).
- Generar recibos PDF.
- Generar reportes financieros.
- Consultar historial de pagos.
- Autorizar el cierre de la nomina.

### Nota tecnica de implementacion

- Los permisos se controlan por rol en el modelo de usuario, en la matriz central de permisos.
- Las rutas se protegen con middleware de permiso para habilitar solo los modulos autorizados.
- El menu lateral muestra solo las opciones para las que el usuario autenticado tiene permiso.
- Algunas funciones del texto funcional (por ejemplo metas de ventas, evaluacion de desempeno o flujo de autorizacion final) dependen de modulos especificos y pueden requerir pantallas adicionales para quedar 1:1.

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

Todos los usuarios pueden iniciar sesion con CURP en /login.

Contrasenas de cuentas de area:

- SUPERVISOR: Supervisor123!
- JEFE_AREA: JefeArea123!

Contrasena de empleados demo:

- EMPLEADO: Empleado123!

### Cuentas por area

| Area | Rol | Usuario | Email | CURP |
|---|---|---|---|---|
| Finanzas | SUPERVISOR | supervisor_finanzas | supervisor.finanzas@sistema.local | PIMF900202HDFRNN12 |
| Finanzas | JEFE_AREA | jefe_finanzas | jefe.finanzas@sistema.local | SOQF910606HDFNNS22 |
| Operaciones | SUPERVISOR | supervisor_demo | supervisor@sistema.local | RANS880303MDFKRL03 |
| Operaciones | JEFE_AREA | jefe_operaciones | jefe.operaciones@sistema.local | BEIO920707HDFPRS23 |
| Recursos Humanos | SUPERVISOR | supervisor_rh | supervisor.rh@sistema.local | MECR890101MDFHSL11 |
| Recursos Humanos | JEFE_AREA | jefe_rh | jefe.rh@sistema.local | MONJ900505HDFRHS21 |
| Tecnologia | SUPERVISOR | supervisor_tecnologia | supervisor.tecnologia@sistema.local | SORT920404MDFLNL14 |
| Tecnologia | JEFE_AREA | jefe_tecnologia | jefe.tecnologia@sistema.local | NALT940909MDFTCN25 |
| Ventas | SUPERVISOR | supervisor_ventas | supervisor.ventas@sistema.local | LURV910303MDFTNS13 |
| Ventas | JEFE_AREA | jefe_ventas | jefe.ventas@sistema.local | CARV930808MDFVTS24 |

### Empleados por area (10 por area)

Finanzas:

- LORA900101MDFPRN01
- CALP970808HDFRST08
- FINA990820HDF19A12
- FINA800921HDF20A13
- FINA811022HDF21A14
- FINA821123HDF22A15
- FINA831224HDF23A16
- FINA840125HDF24A17
- FINA850226HDF25A18
- FINA860327HDF26A19

Operaciones:

- MESC910202HDFNTR02
- VEIR950606HDFBGS06
- OPER870428HDF27A12
- OPER880501HDF28A13
- OPER890602HDF29A14
- OPER900703HDF30A15
- OPER910804HDF31A16
- OPER920905HDF32A17
- OPER931006HDF33A18
- OPER941107HDF34A19

Recursos Humanos:

- GODL920303MDFMZS03
- SAMD960707MDFNRT07
- RECU911212HDF11A12
- RECU920113HDF12A13
- RECU930214HDF13A14
- RECU940315HDF14A15
- RECU950416HDF15A16
- RECU960517HDF16A17
- RECU970618HDF17A18
- RECU980719HDF18A19

Tecnologia:

- OENM940505MDFRVR05
- RATM991010HDFMNG10
- TECN830816HDF43A12
- TECN840917HDF44A13
- TECN851018HDF45A14
- TECN861119HDF46A15
- TECN871220HDF47A16
- TECN880121HDF48A17
- TECN890222HDF49A18
- TECN900323HDF50A19

Ventas:

- HEPJ930404HDFRZG04
- PIFE980909MDFNLS09
- VENT951208HDF35A12
- VENT960109HDF36A13
- VENT970210HDF37A14
- VENT980311HDF38A15
- VENT990412HDF39A16
- VENT800513HDF40A17
- VENT810614HDF41A18
- VENT820715HDF42A19

Comandos para crear o actualizar usuarios demo:

- php artisan db:seed --class=EmpleadoSeeder
- php artisan db:seed --class=UsuariosRolesSeeder

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
