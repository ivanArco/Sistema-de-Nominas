<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\Nomina;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Muestra tablero con KPIs de operacion de nomina.
     */
    public function index(): View
    {
        $kpis = [
            'usuarios_activos' => User::where('activo', true)->count(),
            'clientes_activos' => Cliente::where('estatus', 'ACTIVO')->count(),
            'empleados_activos' => Empleado::where('estatus', 'ACTIVO')->count(),
            'incidencias_mes' => Incidencia::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'nominas_mes' => Nomina::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'neto_pagado_mes' => (float) Nomina::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('neto_pagado'),
        ];

        $nominasRecientes = Nomina::query()
            ->select(['id', 'empleado_id', 'neto_pagado', 'estatus', 'created_at'])
            ->latest()
            ->take(8)
            ->get();

        $incidenciasPorTipo = Incidencia::query()
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->orderByDesc('total')
            ->take(6)
            ->get();

        $empleadosPorEstatus = Empleado::query()
            ->selectRaw('estatus, COUNT(*) as total')
            ->groupBy('estatus')
            ->get();

        $meses = collect(range(5, 0))
            ->map(fn (int $resta) => Carbon::now()->subMonths($resta))
            ->values();

        $nominasPorMes = $meses->map(function (Carbon $mes) {
            return (int) Nomina::query()
                ->whereYear('created_at', $mes->year)
                ->whereMonth('created_at', $mes->month)
                ->count();
        });

        $netoPorMes = $meses->map(function (Carbon $mes) {
            return round((float) Nomina::query()
                ->whereYear('created_at', $mes->year)
                ->whereMonth('created_at', $mes->month)
                ->sum('neto_pagado'), 2);
        });

        $incidenciasData = $incidenciasPorTipo->map(function ($fila) {
            return [
                'tipo' => (string) $fila->tipo,
                'total' => (int) $fila->total,
            ];
        })->values();

        $empleadosData = $empleadosPorEstatus->map(function ($fila) {
            return [
                'estatus' => (string) $fila->estatus,
                'total' => (int) $fila->total,
            ];
        })->values();

        $seriesMensuales = [
            'labels' => $meses->map(fn (Carbon $mes) => strtoupper($mes->locale('es')->translatedFormat('M y')))->values(),
            'nominas' => $nominasPorMes->values(),
            'neto' => $netoPorMes->values(),
        ];

        return view('dashboard.index', [
            'kpis' => $kpis,
            'nominasRecientes' => $nominasRecientes,
            'incidenciasPorTipo' => $incidenciasPorTipo,
            'empleadosPorEstatus' => $empleadosPorEstatus,
            'incidenciasData' => $incidenciasData,
            'empleadosData' => $empleadosData,
            'seriesMensuales' => $seriesMensuales,
            'titulo' => 'Dashboard de Nomina',
        ]);
    }
}
