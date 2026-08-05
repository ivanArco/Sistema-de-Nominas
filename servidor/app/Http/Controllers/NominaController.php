<?php

namespace App\Http\Controllers;

use App\Models\ConceptoNomina;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\Nomina;
use App\Models\NominaDetalle;
use App\Models\PeriodoNomina;
use App\Models\User;
use App\Services\NominaCalculatorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NominaController extends Controller
{
    public function __construct(private readonly NominaCalculatorService $calculatorService)
    {
    }

    public function index(Request $request): View
    {
        $periodoId = $request->string('periodo_nomina_id')->toString();
        $estatus = $request->string('estatus')->toString();

        $nominas = Nomina::query()
            ->with(['empleado', 'periodo'])
            ->when($periodoId !== '', fn ($consulta) => $consulta->where('periodo_nomina_id', $periodoId))
            ->when($estatus !== '', fn ($consulta) => $consulta->where('estatus', $estatus))
            ->tap(fn (Builder $consulta) => $this->aplicarFiltroAreaContadorNominas($consulta, $request))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('nominas.index', [
            'nominas' => $nominas,
            'periodos' => PeriodoNomina::orderByDesc('anio')->orderByDesc('numero_periodo')->get(),
            'filtros' => $request->only(['periodo_nomina_id', 'estatus']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $busquedaEmpleado = $request->string('buscar_empleado')->trim()->toString();
        $tipoPagoFiltro = strtoupper(trim($request->string('tipo_pago')->toString()));
        $hoy = now();

        if (!in_array($tipoPagoFiltro, ['SEMANAL', 'QUINCENAL', 'MENSUAL'], true)) {
            $tipoPagoFiltro = '';
        }

        $empleados = Empleado::query()
            ->with(['departamento', 'puesto'])
            ->where('estatus', 'ACTIVO')
            ->tap(fn (Builder $consulta) => $this->aplicarFiltroAreaContadorEmpleados($consulta, $request))
            ->when($tipoPagoFiltro !== '', fn ($consulta) => $consulta->where('tipo_pago', $tipoPagoFiltro))
            ->when($busquedaEmpleado !== '', function ($consulta) use ($busquedaEmpleado) {
                $consulta->where(function ($subconsulta) use ($busquedaEmpleado) {
                    $subconsulta->where('num_empleado', 'like', "%{$busquedaEmpleado}%")
                        ->orWhere('nombre', 'like', "%{$busquedaEmpleado}%")
                        ->orWhere('ap_paterno', 'like', "%{$busquedaEmpleado}%")
                        ->orWhere('ap_materno', 'like', "%{$busquedaEmpleado}%")
                        ->orWhere('curp', 'like', "%{$busquedaEmpleado}%")
                        ->orWhere('rfc', 'like', "%{$busquedaEmpleado}%");
                });
            })
            ->orderBy('nombre')
            ->orderBy('ap_paterno')
            ->limit(300)
            ->get();

        $empleadosData = $empleados->map(function (Empleado $empleado): array {
            return [
                'id' => $empleado->id,
                'num_empleado' => $empleado->num_empleado,
                'nombre_completo' => trim($empleado->nombre . ' ' . $empleado->ap_paterno . ' ' . ($empleado->ap_materno ?? '')),
                'curp' => $empleado->curp,
                'rfc' => $empleado->rfc,
                'nss' => $empleado->nss,
                'f_ingreso' => optional($empleado->f_ingreso)->format('d/m/Y'),
                'departamento' => $empleado->departamento->nombre ?? '-',
                'puesto' => $empleado->puesto->nombre ?? '-',
                'sal_dia' => number_format((float) $empleado->sal_dia, 2),
                'sal_int' => number_format((float) $empleado->sal_int, 2),
                'tipo_pago' => (string) ($empleado->tipo_pago ?? 'QUINCENAL'),
                'semanas_cotizadas' => number_format((float) ($empleado->semanas_cotizadas ?? 0), 2),
                'fondo_retiro' => number_format((float) ($empleado->fondo_retiro_acumulado ?? 0), 2),
            ];
        })->values();

        return view('nominas.create', [
            'empleados' => $empleados,
            'empleadosData' => $empleadosData,
            'periodos' => PeriodoNomina::where('estatus', 'ABIERTO')
                ->whereYear('fecha_inicio', (int) $hoy->year)
                ->whereMonth('fecha_inicio', (int) $hoy->month)
                ->when($tipoPagoFiltro !== '', function ($consulta) use ($tipoPagoFiltro) {
                    if ($tipoPagoFiltro === 'QUINCENAL') {
                        $consulta->whereIn('tipo_periodo', ['QUINCENAL', 'CATORCENAL']);
                        return;
                    }

                    $consulta->where('tipo_periodo', $tipoPagoFiltro);
                })
                ->orderByDesc('anio')
                ->orderByDesc('numero_periodo')
                ->get(),
            'filtros' => [
                'buscar_empleado' => $busquedaEmpleado,
                'tipo_pago' => $tipoPagoFiltro,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'periodo_nomina_id' => ['required', 'integer', 'exists:periodo_nominas,id'],
            'estatus' => ['nullable', Rule::in(['BORRADOR', 'CALCULADA', 'PAGADA', 'CANCELADA'])],
        ]);

        $empleado = Empleado::findOrFail($datos['empleado_id']);
        $this->autorizarAccesoEmpleadoContador($empleado, $request);
        $periodo = PeriodoNomina::findOrFail($datos['periodo_nomina_id']);

        [$esCompatible, $mensajeCompatibilidad] = $this->validarCompatibilidadPeriodo($empleado, $periodo);

        if (!$esCompatible) {
            return back()
                ->withInput()
            ->with('error', $mensajeCompatibilidad);
        }

        $incidencias = Incidencia::where('empleado_id', $empleado->id)
            ->where('periodo_nomina_id', $periodo->id)
            ->get();

        $resultado = $this->calculatorService->calcular($empleado, $periodo, $incidencias);

        $this->guardarNominaCalculada(
            (int) $datos['empleado_id'],
            (int) $datos['periodo_nomina_id'],
            (string) ($datos['estatus'] ?? 'CALCULADA'),
            $resultado
        );

        return redirect()->route('nominas.index')->with('exito', 'Nomina calculada y guardada correctamente.');
    }

    public function generarMasivo(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'periodo_nomina_id' => ['required', 'integer', 'exists:periodo_nominas,id'],
            'tipo_pago' => ['required', Rule::in(['SEMANAL', 'QUINCENAL', 'MENSUAL'])],
            'estatus' => ['nullable', Rule::in(['BORRADOR', 'CALCULADA', 'PAGADA', 'CANCELADA'])],
        ]);

        $periodo = PeriodoNomina::findOrFail($datos['periodo_nomina_id']);
        $tipoPagoObjetivo = strtoupper((string) $datos['tipo_pago']);

        $empleados = Empleado::query()
            ->where('estatus', 'ACTIVO')
            ->with(['departamento', 'puesto'])
            ->tap(fn (Builder $consulta) => $this->aplicarFiltroAreaContadorEmpleados($consulta, $request))
            ->get()
            ->filter(fn (Empleado $empleado) => $this->normalizarTipoPago((string) ($empleado->tipo_pago ?? '')) === $tipoPagoObjetivo)
            ->values();

        if ($empleados->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'No hay empleados activos para el tipo de pago seleccionado.');
        }

        $generadas = 0;
        $yaExistentes = 0;
        $omitidas = 0;

        foreach ($empleados as $empleado) {
            [$esCompatible] = $this->validarCompatibilidadPeriodo($empleado, $periodo);
            if (!$esCompatible) {
                $omitidas++;
                continue;
            }

            $nominaExistente = Nomina::query()
                ->where('empleado_id', $empleado->id)
                ->where('periodo_nomina_id', $periodo->id)
                ->exists();

            if ($nominaExistente) {
                $yaExistentes++;
                continue;
            }

            $incidencias = Incidencia::where('empleado_id', $empleado->id)
                ->where('periodo_nomina_id', $periodo->id)
                ->get();

            $resultado = $this->calculatorService->calcular($empleado, $periodo, $incidencias);

            $this->guardarNominaCalculada(
                (int) $empleado->id,
                (int) $periodo->id,
                (string) ($datos['estatus'] ?? 'CALCULADA'),
                $resultado
            );

            $generadas++;
        }

        if ($generadas === 0) {
            return back()
                ->withInput()
                ->with('error', 'No se genero ninguna nomina nueva. Ya existen nominas para ese periodo o el periodo no corresponde al tipo de pago.');
        }

        $mensaje = 'Nominas generadas: ' . $generadas . '.';
        if ($yaExistentes > 0) {
            $mensaje .= ' Ya existentes (no regeneradas): ' . $yaExistentes . '.';
        }
        if ($omitidas > 0) {
            $mensaje .= ' Omitidas por incompatibilidad de periodo: ' . $omitidas . '.';
        }

        return redirect()->route('nominas.index')->with('exito', $mensaje);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): RedirectResponse
    {
        return redirect()->route('nominas.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $nomina = Nomina::with(['empleado.puesto', 'periodo', 'detalles.concepto'])->findOrFail($id);
        $this->autorizarAccesoNominaContador($nomina, request());

        $incidenciasFalta = Incidencia::query()
            ->where('empleado_id', $nomina->empleado_id)
            ->where('periodo_nomina_id', $nomina->periodo_nomina_id)
            ->where('tipo', 'FALTA')
            ->latest()
            ->get();

        $diasFalta = (float) $incidenciasFalta->sum(fn ($incidencia) => (float) $incidencia->cantidad);
        $totalDescuentoFaltas = (float) $incidenciasFalta->sum(fn ($incidencia) => (float) $incidencia->monto);
        $descuentosPorIncidencias = (float) optional($nomina->detalles->firstWhere('concepto.clave', 'D090'))->importe;
        $diasTrabajados = max(0.0, (float) $nomina->dias_pagados - $diasFalta);

        return view('nominas.edit', [
            'nomina' => $nomina,
            'dias_falta' => round($diasFalta, 2),
            'dias_trabajados' => round($diasTrabajados, 2),
            'descuentos_por_incidencias' => round($descuentosPorIncidencias, 2),
            'total_descuento_faltas' => round($totalDescuentoFaltas, 2),
            'incidencias_falta' => $incidenciasFalta,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $nomina = Nomina::findOrFail($id);
        $this->autorizarAccesoNominaContador($nomina, $request);

        $datos = $request->validate([
            'estatus' => ['required', Rule::in(['BORRADOR', 'CALCULADA', 'PAGADA', 'CANCELADA'])],
        ]);

        $nomina->update($datos);

        return redirect()->route('nominas.index')->with('exito', 'Estatus de nomina actualizado correctamente.');
    }

    public function autorizarCierre(Request $request, string $id): RedirectResponse
    {
        $nomina = Nomina::findOrFail($id);
        $this->autorizarAccesoNominaContador($nomina, $request);

        if ($nomina->estatus === 'CANCELADA') {
            return back()->with('error', 'No puedes autorizar cierre de una nomina cancelada.');
        }

        $datos = $request->validate([
            'cierre_observaciones' => ['nullable', 'string', 'max:1500'],
        ]);

        $nomina->update([
            'cierre_autorizado' => true,
            'cierre_autorizado_por' => Auth::id(),
            'fecha_cierre_autorizado' => now(),
            'cierre_observaciones' => $datos['cierre_observaciones'] ?? null,
            'estatus' => 'PAGADA',
        ]);

        return back()->with('exito', 'Cierre de nomina autorizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $nomina = Nomina::findOrFail($id);
        $this->autorizarAccesoNominaContador($nomina, request());
        $nomina->delete();

        return redirect()->route('nominas.index')->with('exito', 'Nomina eliminada correctamente.');
    }

    private function validarCompatibilidadPeriodo(Empleado $empleado, PeriodoNomina $periodo): array
    {
        $tipoPagoOriginal = (string) ($empleado->tipo_pago ?? '');
        $tipoPeriodoOriginal = (string) ($periodo->tipo_periodo ?? '');

        $tipoPago = $this->normalizarTipoPago($tipoPagoOriginal);
        $tipoPeriodo = $this->normalizarTipoPeriodo($tipoPeriodoOriginal);

        $tiposPermitidos = $this->tiposPeriodoPermitidosPorTipoPago($tipoPago);

        if ($tiposPermitidos === []) {
            return [true, ''];
        }

        if (in_array($tipoPeriodo, $tiposPermitidos, true)) {
            return [true, ''];
        }

        $permitidosTexto = implode(', ', $tiposPermitidos);
        $tipoPagoMostrar = $tipoPago !== '' ? $tipoPago : strtoupper(trim($tipoPagoOriginal));
        $tipoPeriodoMostrar = $tipoPeriodo !== '' ? $tipoPeriodo : strtoupper(trim($tipoPeriodoOriginal));

        return [
            false,
            "El periodo seleccionado ({$tipoPeriodoMostrar}) no coincide con el tipo de pago del empleado ({$tipoPagoMostrar}). "
            . "Para este empleado use: {$permitidosTexto}.",
        ];
    }

    private function normalizarTipoPago(string $tipoPago): string
    {
        $valor = $this->normalizarTextoCatalogo($tipoPago);

        return match ($valor) {
            'SEMANAL' => 'SEMANAL',
            'QUINCENAL', 'CATORCENAL', '14 DIAS', 'CADA 14 DIAS' => 'QUINCENAL',
            'MENSUAL', 'MENSUALIDAD' => 'MENSUAL',
            default => '',
        };
    }

    private function normalizarTipoPeriodo(string $tipoPeriodo): string
    {
        $valor = $this->normalizarTextoCatalogo($tipoPeriodo);

        return match ($valor) {
            'SEMANAL' => 'SEMANAL',
            'QUINCENAL', 'CATORCENAL', '14 DIAS', 'CADA 14 DIAS' => 'CATORCENAL',
            'MENSUAL', 'MENSUALIDAD' => 'MENSUAL',
            default => '',
        };
    }

    private function tiposPeriodoPermitidosPorTipoPago(string $tipoPago): array
    {
        return match ($tipoPago) {
            'SEMANAL' => ['SEMANAL'],
            'QUINCENAL' => ['QUINCENAL', 'CATORCENAL'],
            'MENSUAL' => ['MENSUAL'],
            default => [],
        };
    }

    private function normalizarTextoCatalogo(string $valor): string
    {
        return Str::of($valor)
            ->upper()
            ->ascii()
            ->replace(['-', '_'], ' ')
            ->squish()
            ->toString();
    }

    private function guardarNominaCalculada(int $empleadoId, int $periodoId, string $estatus, array $resultado): void
    {
        DB::transaction(function () use ($empleadoId, $periodoId, $estatus, $resultado) {
            $nomina = Nomina::updateOrCreate(
                [
                    'empleado_id' => $empleadoId,
                    'periodo_nomina_id' => $periodoId,
                ],
                [
                    'dias_pagados' => $resultado['dias_pagados'],
                    'total_percepciones' => $resultado['total_percepciones'],
                    'total_deducciones' => $resultado['total_deducciones'],
                    'neto_pagado' => $resultado['neto_pagado'],
                    'estatus' => $estatus,
                ]
            );

            NominaDetalle::where('nomina_id', $nomina->id)->delete();

            foreach ($resultado['detalles'] as $detalle) {
                if ((float) $detalle['importe'] <= 0) {
                    continue;
                }

                $concepto = ConceptoNomina::firstOrCreate(
                    ['clave' => $detalle['clave']],
                    [
                        'nombre' => $detalle['nombre'],
                        'tipo' => $detalle['tipo'],
                        'gravado' => $detalle['tipo'] !== 'OTRO_PAGO',
                        'activo' => true,
                    ]
                );

                NominaDetalle::create([
                    'nomina_id' => $nomina->id,
                    'concepto_nomina_id' => $concepto->id,
                    'cantidad' => 1,
                    'importe' => $detalle['importe'],
                    'observaciones' => 'Generado automaticamente en el calculo de nomina.',
                ]);
            }
        });
    }

    private function aplicarFiltroAreaContadorNominas(Builder $consulta, Request $request): void
    {
        if (!$this->esContador($request)) {
            return;
        }

        $area = $this->resolverAreaContador($request);

        if ($area === null) {
            $consulta->whereRaw('1 = 0');

            return;
        }

        $consulta->whereHas('empleado.departamento', function (Builder $subconsulta) use ($area): void {
            $subconsulta->whereRaw('LOWER(nombre) = ?', [Str::lower($area)]);
        });
    }

    private function aplicarFiltroAreaContadorEmpleados(Builder $consulta, Request $request): void
    {
        if (!$this->esContador($request)) {
            return;
        }

        $area = $this->resolverAreaContador($request);

        if ($area === null) {
            $consulta->whereRaw('1 = 0');

            return;
        }

        $consulta->whereHas('departamento', function (Builder $subconsulta) use ($area): void {
            $subconsulta->whereRaw('LOWER(nombre) = ?', [Str::lower($area)]);
        });
    }

    private function autorizarAccesoEmpleadoContador(Empleado $empleado, Request $request): void
    {
        if (!$this->esContador($request)) {
            return;
        }

        $area = $this->resolverAreaContador($request);
        abort_if($area === null, 403, 'Tu usuario contador no tiene area asignada.');

        $nombreDepartamento = $empleado->relationLoaded('departamento')
            ? (string) ($empleado->departamento->nombre ?? '')
            : (string) $empleado->departamento()->value('nombre');

        abort_if(Str::lower(trim($nombreDepartamento)) !== Str::lower($area), 403, 'Solo puedes calcular nominas de empleados de tu area.');
    }

    private function autorizarAccesoNominaContador(Nomina $nomina, Request $request): void
    {
        if (!$this->esContador($request)) {
            return;
        }

        $empleado = Empleado::with('departamento')->findOrFail((int) $nomina->empleado_id);
        $this->autorizarAccesoEmpleadoContador($empleado, $request);
    }

    private function esContador(Request $request): bool
    {
        $usuario = $request->user();

        if (!$usuario instanceof User) {
            return false;
        }

        $rol = $usuario->rolNormalizado();

        if ($rol === 'CONTADOR') {
            return true;
        }

        return $rol === 'JEFE_AREA' && !$usuario->esAdministrador();
    }

    private function resolverAreaContador(Request $request): ?string
    {
        if (!$this->esContador($request)) {
            return null;
        }

        $area = trim((string) ($request->user()?->area_contratacion ?? ''));

        return $area !== '' ? $area : null;
    }
}
