<?php

use App\Http\Controllers\ConceptoNominaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\NominaController;
use App\Http\Controllers\PeriodoNominaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AutenticacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AutenticacionController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AutenticacionController::class, 'iniciarSesion'])->name('login.iniciar');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AutenticacionController::class, 'cerrarSesion'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:JEFE_AREA,SECRETARIA,SUPERVISOR')->group(function () {
        Route::resource('usuarios', UsuarioController::class);
    });

    Route::middleware('role:JEFE_AREA,SECRETARIA,SUPERVISOR,VENDEDOR')->group(function () {
        Route::resource('clientes', ClienteController::class);
    });

    Route::middleware('role:JEFE_AREA,SECRETARIA,SUPERVISOR')->group(function () {
        Route::resource('empleados', EmpleadoController::class);
    });

    Route::middleware('role:JEFE_AREA,CONTADOR,SUPERVISOR,SECRETARIA')->group(function () {
        Route::get('/catalogos-nomina', [\App\Http\Controllers\CatalogoNominaController::class, 'index'])->name('catalogos-nomina.index');

        Route::resources([
            'periodos-nomina' => PeriodoNominaController::class,
            'conceptos-nomina' => ConceptoNominaController::class,
            'incidencias' => IncidenciaController::class,
            'nominas' => NominaController::class,
        ]);

        Route::post('nominas/generar-masivo', [NominaController::class, 'generarMasivo'])
            ->name('nominas.generar-masivo');

        Route::post('periodos-nomina/generar-automaticos', [PeriodoNominaController::class, 'generarAutomaticos'])
            ->name('periodos-nomina.generar-automaticos');

        Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('reportes/nominas', [ReporteController::class, 'nominas'])->name('reportes.nominas');
        Route::get('reportes/nominas/pdf', [ReporteController::class, 'nominasPdf'])->name('reportes.nominas.pdf');
        Route::get('reportes/nominas/csv', [ReporteController::class, 'nominasCsv'])->name('reportes.nominas.csv');
        Route::get('reportes/empleados', [ReporteController::class, 'empleados'])->name('reportes.empleados');
        Route::get('reportes/incidencias', [ReporteController::class, 'incidencias'])->name('reportes.incidencias');

        Route::get('usuarios-reporte/pdf', [UsuarioController::class, 'reportePdf'])
            ->name('usuarios.reporte.pdf');

        Route::get('clientes-reporte/pdf', [ClienteController::class, 'reportePdf'])
            ->name('clientes.reporte.pdf');
    });
});
