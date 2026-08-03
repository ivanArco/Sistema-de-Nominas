<?php

namespace App\Http\Controllers;

use App\Models\PeriodoNomina;
use App\Services\PeriodoNominaGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PeriodoNominaController extends Controller
{
    public function __construct(private readonly PeriodoNominaGeneratorService $periodoGenerator)
    {
    }

    public function index(Request $request): View
    {
        $tipoPeriodo = $request->string('tipo_periodo')->toString();
        $estatus = $request->string('estatus')->toString();
        $anioTexto = $request->string('anio')->trim()->toString();
        $mesTexto = $request->string('mes')->trim()->toString();

        $anio = ctype_digit($anioTexto) ? (int) $anioTexto : null;
        $mes = ctype_digit($mesTexto) ? (int) $mesTexto : null;

        if ($anio !== null && ($anio < 2020 || $anio > 2100)) {
            $anio = null;
        }

        if ($mes !== null && ($mes < 1 || $mes > 12)) {
            $mes = null;
        }

        $periodos = PeriodoNomina::query()
            ->when($anio !== null, fn ($consulta) => $consulta->whereYear('fecha_inicio', $anio))
            ->when($mes !== null, fn ($consulta) => $consulta->whereMonth('fecha_inicio', $mes))
            ->when($tipoPeriodo !== '', fn ($consulta) => $consulta->where('tipo_periodo', $tipoPeriodo))
            ->when($estatus !== '', fn ($consulta) => $consulta->where('estatus', $estatus))
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('periodos_nomina.index', [
            'periodos' => $periodos,
            'filtros' => [
                'tipo_periodo' => $tipoPeriodo,
                'estatus' => $estatus,
                'anio' => $anio,
                'mes' => $mes,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('periodos_nomina.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        PeriodoNomina::create($this->validarDatosPeriodo($request));

        return redirect()->route('periodos-nomina.index')->with('exito', 'Periodo registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): RedirectResponse
    {
        return redirect()->route('periodos-nomina.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        return view('periodos_nomina.edit', [
            'periodo' => PeriodoNomina::findOrFail($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $periodo = PeriodoNomina::findOrFail($id);
        $periodo->update($this->validarDatosPeriodo($request, $periodo->id));

        return redirect()->route('periodos-nomina.index')->with('exito', 'Periodo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        PeriodoNomina::findOrFail($id)->delete();

        return redirect()->route('periodos-nomina.index')->with('exito', 'Periodo eliminado correctamente.');
    }

    public function generarAutomaticos(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'anio' => ['required', 'integer', 'min:2020', 'max:2100'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $resultado = $this->periodoGenerator->generarParaMes((int) $datos['anio'], (int) $datos['mes']);

        return redirect()
            ->route('periodos-nomina.index', ['anio' => $datos['anio'], 'mes' => $datos['mes']])
            ->with('exito', 'Generacion completada. Nuevos: ' . $resultado['creados'] . ' | Existentes: ' . $resultado['existentes']);
    }

    private function validarDatosPeriodo(Request $request, ?int $periodoId = null): array
    {
        return $request->validate([
            'anio' => ['required', 'integer', 'min:2020', 'max:2100'],
            'numero_periodo' => ['required', 'integer', 'min:1', 'max:60'],
            'tipo_periodo' => ['required', Rule::in(['SEMANAL', 'QUINCENAL', 'MENSUAL'])],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'fecha_pago' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estatus' => ['required', Rule::in(['ABIERTO', 'CALCULADO', 'CERRADO', 'TIMBRADO'])],
        ]);
    }
}
