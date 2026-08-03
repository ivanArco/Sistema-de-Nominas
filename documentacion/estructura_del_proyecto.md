# Estructura del proyecto

## Carpetas principales

- servidor: backend Laravel con controladores, modelos, servicios, rutas y base de datos.
- interfaz: recursos de la interfaz de usuario y assets del frontend.
- base_de_datos: scripts y esquemas SQL del sistema.
- documentacion: manuales, especificaciones y referencias del proyecto.
- herramientas: utilidades y scripts de apoyo.

## Arquitectura Laravel configurada (servidor)

- app/Console: comandos Artisan personalizados.
- app/Domain: modulos de negocio por contexto (Nomina, Empleado, Cliente, Usuario, Incidencia, Catalogo).
- app/Events: eventos de dominio y aplicacion.
- app/Exceptions: manejo de excepciones de negocio y aplicacion.
- app/Http/Controllers: controladores HTTP por modulo.
- app/Http/Middleware: middlewares de autenticacion, autorizacion y filtros.
- app/Http/Requests: validaciones por caso de uso (Form Requests).
- app/Http/Resources: transformaciones de salida para respuestas JSON.
- app/Infrastructure/Persistence: implementaciones de persistencia y repositorios.
- app/Jobs: tareas en cola.
- app/Listeners: listeners para eventos.
- app/Mail: clases de correo.
- app/Models: modelos Eloquent.
- app/Notifications: notificaciones del sistema.
- app/Observers: observers de modelos.
- app/Policies: politicas de autorizacion.
- app/Providers: proveedores de servicios de Laravel.
- app/Rules: reglas de validacion reutilizables.
- app/Services: servicios de aplicacion existentes.
- app/Support: utilidades y helpers compartidos.
- app/Traits: traits reutilizables.

## Convencion recomendada

- Mantener la logica de negocio por modulo en app/Domain.
- Dejar los controladores delgados y delegar reglas a Services o Domain.
- Usar Http/Requests para validacion de entrada y Policies para autorizacion.
- Ubicar acceso a datos avanzado en Infrastructure/Persistence/Repositories.

## Uso recomendado

- Mantener toda la lógica de negocio en servidor.
- Mantener los archivos de presentación en interfaz.
- Usar documentacion y base_de_datos para referencia y mantenimiento.
