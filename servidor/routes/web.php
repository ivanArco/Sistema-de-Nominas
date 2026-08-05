<?php

use App\Http\Controllers\ConceptoNominaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\MiCuentaController;
use App\Http\Controllers\NominaController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\PeriodoNominaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\SolicitudAprobacionController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\AutenticacionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EvaluacionDesempenoController;
use App\Http\Controllers\VentaMetaController;

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    /** @var \App\Models\User|null $usuario */
    $usuario = Auth::user();

    if ($usuario?->tienePermiso('dashboard.ver')) {
        return redirect()->route('dashboard');
    }

    if ($usuario?->tienePermiso('autoservicio.ver')) {
        return redirect()->route('mi-cuenta.index');
    }

    if ($usuario?->tienePermiso('solicitudes.aprobar')) {
        return redirect()->route('solicitudes.aprobacion.index');
    }

    abort(403, 'No tienes una ruta de inicio habilitada.');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AutenticacionController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AutenticacionController::class, 'iniciarSesion'])->name('login.iniciar');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AutenticacionController::class, 'cerrarSesion'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permiso:dashboard.ver')
        ->name('dashboard');

    // ---------------------------------------------------------------
    // Usuarios: permisos divididos por accion.
    // - usuarios.consultar : solo lectura (listado, detalle, reporte).
    // - usuarios.gestionar : crear y editar (implica tambien poder consultar).
    // - usuarios.eliminar  : eliminar. En la configuracion actual lo tiene JEFE_AREA.
    // ---------------------------------------------------------------
    Route::middleware('permiso:usuarios.consultar,usuarios.gestionar')->group(function () {
        Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    });

    Route::middleware('permiso:usuarios.gestionar')->group(function () {
        Route::get('usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::patch('usuarios/{usuario}', [UsuarioController::class, 'update']);
    });

    // Roles y permisos: solo para administrador.
    Route::middleware(['permiso:usuarios.gestionar', 'admin'])->group(function () {
        Route::resource('roles', RolController::class);
        Route::resource('permisos', PermisoController::class)->except(['show']);
    });

    Route::middleware('permiso:usuarios.consultar,usuarios.gestionar')->group(function () {
        Route::get('usuarios/{usuario}', [UsuarioController::class, 'show'])->name('usuarios.show');
        Route::get('usuarios-reporte/pdf', [UsuarioController::class, 'reportePdf'])->name('usuarios.reporte.pdf');
    });

    Route::middleware('permiso:usuarios.eliminar')->group(function () {
        Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    });

    Route::middleware('permiso:clientes.gestionar')->group(function () {
        Route::resource('clientes', ClienteController::class);
    });

    Route::middleware('permiso:empleados.gestionar')->group(function () {
        Route::resource('empleados', EmpleadoController::class);
    });

    Route::middleware('permiso:catalogos_nomina.gestionar')->group(function () {
        Route::get('/catalogos-nomina', [\App\Http\Controllers\CatalogoNominaController::class, 'index'])->name('catalogos-nomina.index');

        Route::resources([
            'periodos-nomina' => PeriodoNominaController::class,
            'conceptos-nomina' => ConceptoNominaController::class,
        ]);

        Route::post('periodos-nomina/generar-automaticos', [PeriodoNominaController::class, 'generarAutomaticos'])
            ->name('periodos-nomina.generar-automaticos');
    });

    Route::middleware('permiso:incidencias.gestionar')->group(function () {
        Route::resource('incidencias', IncidenciaController::class);
    });

    Route::middleware('permiso:nominas.gestionar')->group(function () {
        Route::resource('nominas', NominaController::class);

        Route::post('nominas/generar-masivo', [NominaController::class, 'generarMasivo'])
            ->name('nominas.generar-masivo');
    });

    Route::middleware('permiso:nominas.cierre.autorizar')->group(function () {
        Route::patch('nominas/{nomina}/autorizar-cierre', [NominaController::class, 'autorizarCierre'])
            ->name('nominas.autorizar-cierre');
    });

    Route::middleware('permiso:reportes.ver')->group(function () {
        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('reportes/nominas', [ReporteController::class, 'nominas'])->name('reportes.nominas');
        Route::get('reportes/nominas/pdf', [ReporteController::class, 'nominasPdf'])->name('reportes.nominas.pdf');
        Route::post('reportes/nominas/pdf-seleccionadas', [ReporteController::class, 'nominasPdf'])->name('reportes.nominas.pdf.seleccionadas');
        Route::get('reportes/nominas/csv', [ReporteController::class, 'nominasCsv'])->name('reportes.nominas.csv');
        Route::get('reportes/empleados', [ReporteController::class, 'empleados'])->name('reportes.empleados');
        Route::get('reportes/incidencias', [ReporteController::class, 'incidencias'])->name('reportes.incidencias');
    });

    Route::middleware('permiso:clientes.gestionar')->group(function () {
        Route::get('clientes-reporte/pdf', [ClienteController::class, 'reportePdf'])
            ->name('clientes.reporte.pdf');
    });

    Route::middleware('permiso:autoservicio.ver')->group(function () {
        Route::get('mi-cuenta', [MiCuentaController::class, 'index'])->name('mi-cuenta.index');
        Route::get('mi-cuenta/editar', [MiCuentaController::class, 'edit'])->name('mi-cuenta.edit')->middleware('permiso:mi-cuenta.editar');
        Route::put('mi-cuenta', [MiCuentaController::class, 'update'])->name('mi-cuenta.update')->middleware('permiso:mi-cuenta.editar');
        Route::get('mi-cuenta/recibos', [MiCuentaController::class, 'recibos'])->name('mi-cuenta.recibos');
        Route::get('mi-cuenta/recibos/{nomina}/pdf', [MiCuentaController::class, 'reciboPdf'])->name('mi-cuenta.recibo.pdf');
    });

    Route::middleware('permiso:autoservicio.password')->group(function () {
        Route::get('mi-cuenta/password', [MiCuentaController::class, 'passwordForm'])->name('mi-cuenta.password.form');
        Route::put('mi-cuenta/password', [MiCuentaController::class, 'updatePassword'])->name('mi-cuenta.password.update');
    });

    Route::middleware('permiso:solicitudes.propias')->group(function () {
        Route::get('mis-solicitudes', [SolicitudController::class, 'index'])->name('solicitudes.index');
        Route::get('mis-solicitudes/crear', [SolicitudController::class, 'create'])->name('solicitudes.create');
        Route::post('mis-solicitudes', [SolicitudController::class, 'store'])->name('solicitudes.store');
    });

    Route::middleware('permiso:solicitudes.aprobar')->group(function () {
        Route::get('solicitudes-aprobacion', [SolicitudAprobacionController::class, 'index'])->name('solicitudes.aprobacion.index');
        Route::patch('solicitudes-aprobacion/{solicitud}', [SolicitudAprobacionController::class, 'update'])->name('solicitudes.aprobacion.update');
    });

    Route::middleware('permiso:asistencias.gestionar')->group(function () {
        Route::resource('asistencias', AsistenciaController::class);
    });

    Route::middleware('permiso:expedientes.gestionar')->group(function () {
        Route::resource('expedientes', ExpedienteController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    });

    Route::middleware('permiso:ventas.gestionar,ventas.propias')->group(function () {
        Route::resource('ventas', VentaController::class);
    });

    Route::middleware('permiso:bonos.autorizar')->group(function () {
        Route::patch('ventas/{venta}/autorizar-bono', [VentaController::class, 'autorizarBono'])
            ->name('ventas.autorizar-bono');
    });

    Route::middleware('permiso:ventas.metas.ver')->group(function () {
        Route::resource('ventas-metas', VentaMetaController::class)
            ->only(['index'])
            ->parameters(['ventas-metas' => 'meta'])
            ->names('ventas.metas');
    });

    Route::middleware('permiso:ventas.metas.gestionar')->group(function () {
        Route::resource('ventas-metas', VentaMetaController::class)
            ->except(['index', 'show'])
            ->parameters(['ventas-metas' => 'meta'])
            ->names('ventas.metas');
    });

    Route::middleware('permiso:evaluaciones.gestionar')->group(function () {
        Route::resource('evaluaciones', EvaluacionDesempenoController::class)->except(['show']);
    });
});