<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\Nomina;
use App\Models\User;
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

        return view('dashboard.index', [
            'kpis' => $kpis,
            'nominasRecientes' => $nominasRecientes,
            'incidenciasPorTipo' => $incidenciasPorTipo,
            'empleadosPorEstatus' => $empleadosPorEstatus,
            'titulo' => 'Dashboard de Nomina',
        ]);
    }
}
