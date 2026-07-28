<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\PeriodoNomina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IncidenciaController extends Controller
{
    public function index(Request $request): View
    {
        $periodoId = $request->string('periodo_nomina_id')->toString();

        $incidencias = Incidencia::query()
            ->with(['empleado', 'periodo'])
            ->when($periodoId !== '', fn ($consulta) => $consulta->where('periodo_nomina_id', $periodoId))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('incidencias.index', [
            'incidencias' => $incidencias,
            'periodos' => PeriodoNomina::orderByDesc('anio')->orderByDesc('numero_periodo')->get(),
            'filtros' => $request->only(['periodo_nomina_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('incidencias.create', [
            'empleados' => Empleado::where('estatus', 'ACTIVO')->orderBy('nombre')->get(),
            'periodos' => PeriodoNomina::orderByDesc('anio')->orderByDesc('numero_periodo')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Incidencia::create($this->prepararDatosIncidencia($request));

        return redirect()->route('incidencias.index')->with('exito', 'Incidencia registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): RedirectResponse
    {
        return redirect()->route('incidencias.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        return view('incidencias.edit', [
            'incidencia' => Incidencia::findOrFail($id),
            'empleados' => Empleado::where('estatus', 'ACTIVO')->orderBy('nombre')->get(),
            'periodos' => PeriodoNomina::orderByDesc('anio')->orderByDesc('numero_periodo')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $incidencia = Incidencia::findOrFail($id);
        $incidencia->update($this->prepararDatosIncidencia($request));

        return redirect()->route('incidencias.index')->with('exito', 'Incidencia actualizada correctamente.');
    }

    private function prepararDatosIncidencia(Request $request): array
    {
        $datos = $this->validarDatosIncidencia($request);

        $empleado = Empleado::find($datos['empleado_id']);
        $salarioDiario = (float) ($empleado->sal_dia ?? 0);

        $datos['monto'] = $this->resolverMontoIncidencia(
            (string) $datos['tipo'],
            (float) $datos['cantidad'],
            (float) $datos['monto'],
            $salarioDiario
        );

        return $datos;
    }

    private function resolverMontoIncidencia(string $tipo, float $cantidad, float $monto, float $salarioDiario): float
    {
        if ($tipo === 'FALTA' && $cantidad > 0 && $salarioDiario > 0) {
            return round($cantidad * $salarioDiario, 2);
        }

        if ($monto > 0) {
            return round($monto, 2);
        }

        if ($tipo === 'DESCANSO') {
            return 0.0;
        }

        $tiposPorDia = ['FALTA', 'VACACIONES', 'VACACIONES_PAGADAS'];
        if ($cantidad > 0 && in_array($tipo, $tiposPorDia, true) && $salarioDiario > 0) {
            return round($cantidad * $salarioDiario, 2);
        }

        // Fallback: si no se captura monto, usar cantidad como importe directo.
        return $cantidad > 0 ? round($cantidad, 2) : 0.0;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        Incidencia::findOrFail($id)->delete();

        return redirect()->route('incidencias.index')->with('exito', 'Incidencia eliminada correctamente.');
    }

    private function validarDatosIncidencia(Request $request): array
    {
        return $request->validate([
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'periodo_nomina_id' => ['required', 'integer', 'exists:periodo_nominas,id'],
            'tipo' => ['required', Rule::in(['FALTA', 'RETARDO', 'HORA_EXTRA', 'BONO', 'INCAPACIDAD', 'VACACIONES', 'VACACIONES_PAGADAS', 'DESCANSO', 'OTRO'])],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'cantidad' => ['required', 'numeric', 'min:0'],
            'monto' => ['required', 'numeric', 'min:0'],
        ]);
    }
}
