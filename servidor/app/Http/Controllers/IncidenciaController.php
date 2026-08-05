<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\PeriodoNomina;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            ->tap(fn (Builder $consulta) => $this->aplicarFiltroAreaIncidencias($consulta, $request))
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
        $request = request();

        return view('incidencias.create', [
            'empleados' => $this->consultarEmpleadosDisponibles($request)->orderBy('nombre')->get(),
            'periodos' => PeriodoNomina::orderByDesc('anio')->orderByDesc('numero_periodo')->get(),
            'alcanceDepartamento' => $this->resolverAreaGestion($request),
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
        $request = request();
        $incidencia = Incidencia::with('empleado.departamento')->findOrFail($id);
        $this->autorizarEmpleadoPorArea((int) $incidencia->empleado_id, $request);

        return view('incidencias.edit', [
            'incidencia' => $incidencia,
            'empleados' => $this->consultarEmpleadosDisponibles($request)->orderBy('nombre')->get(),
            'periodos' => PeriodoNomina::orderByDesc('anio')->orderByDesc('numero_periodo')->get(),
            'alcanceDepartamento' => $this->resolverAreaGestion($request),
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
        $this->autorizarEmpleadoPorArea((int) $datos['empleado_id'], $request);

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

    private function consultarEmpleadosDisponibles(Request $request): Builder
    {
        $consulta = Empleado::query()
            ->with('departamento')
            ->where('estatus', 'ACTIVO');

        $this->aplicarFiltroAreaEmpleados($consulta, $request);

        return $consulta;
    }

    private function aplicarFiltroAreaIncidencias(Builder $consulta, Request $request): void
    {
        if (!$this->usuarioRestringidoPorArea($request)) {
            return;
        }

        $area = $this->resolverAreaGestion($request);

        if ($area === null) {
            $consulta->whereRaw('1 = 0');

            return;
        }

        $consulta->whereHas('empleado.departamento', function (Builder $subconsulta) use ($area): void {
            $subconsulta->whereRaw('LOWER(nombre) = ?', [Str::lower($area)]);
        });
    }

    private function aplicarFiltroAreaEmpleados(Builder $consulta, Request $request): void
    {
        if (!$this->usuarioRestringidoPorArea($request)) {
            return;
        }

        $area = $this->resolverAreaGestion($request);

        if ($area === null) {
            $consulta->whereRaw('1 = 0');

            return;
        }

        $consulta->whereHas('departamento', function (Builder $subconsulta) use ($area): void {
            $subconsulta->whereRaw('LOWER(nombre) = ?', [Str::lower($area)]);
        });
    }

    private function autorizarEmpleadoPorArea(int $empleadoId, Request $request): void
    {
        if (!$this->usuarioRestringidoPorArea($request)) {
            return;
        }

        $area = $this->resolverAreaGestion($request);
        abort_if($area === null, 403, 'Tu usuario no tiene departamento asignado.');

        $existeEmpleadoAutorizado = Empleado::query()
            ->whereKey($empleadoId)
            ->whereHas('departamento', function (Builder $subconsulta) use ($area): void {
                $subconsulta->whereRaw('LOWER(nombre) = ?', [Str::lower($area)]);
            })
            ->exists();

        abort_if(!$existeEmpleadoAutorizado, 403, 'Solo puedes gestionar incidencias de empleados de tu departamento.');
    }

    private function usuarioRestringidoPorArea(Request $request): bool
    {
        $usuario = $request->user();

        return $usuario instanceof User && $usuario->tieneAlgunRol(['SUPERVISOR', 'JEFE_AREA']);
    }

    private function resolverAreaGestion(Request $request): ?string
    {
        if (!$this->usuarioRestringidoPorArea($request)) {
            return null;
        }

        $area = trim((string) ($request->user()?->area_contratacion ?? ''));

        return $area !== '' ? $area : null;
    }
}
