<?php

use App\Http\Controllers\ConceptoNominaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\NominaController;
use App\Http\Controllers\PeriodoNominaController;
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

    Route::resources([
        'usuarios' => UsuarioController::class,
        'clientes' => ClienteController::class,
        'empleados' => EmpleadoController::class,
        'periodos-nomina' => PeriodoNominaController::class,
        'conceptos-nomina' => ConceptoNominaController::class,
        'incidencias' => IncidenciaController::class,
        'nominas' => NominaController::class,
    ]);

    Route::get('usuarios-reporte/pdf', [UsuarioController::class, 'reportePdf'])
        ->name('usuarios.reporte.pdf');

    Route::get('clientes-reporte/pdf', [ClienteController::class, 'reportePdf'])
        ->name('clientes.reporte.pdf');
});
