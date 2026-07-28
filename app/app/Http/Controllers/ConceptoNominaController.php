<?php

namespace App\Http\Controllers;

use App\Models\ConceptoNomina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConceptoNominaController extends Controller
{
    public function index(Request $request): View
    {
        $tipo = $request->string('tipo')->toString();
        $activo = $request->string('activo')->toString();

        $conceptos = ConceptoNomina::query()
            ->when($tipo !== '', fn ($consulta) => $consulta->where('tipo', $tipo))
            ->when($activo !== '', fn ($consulta) => $consulta->where('activo', $activo === '1'))
            ->orderBy('tipo')
            ->orderBy('clave')
            ->paginate(12)
            ->withQueryString();

        return view('conceptos_nomina.index', [
            'conceptos' => $conceptos,
            'filtros' => $request->only(['tipo', 'activo']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('conceptos_nomina.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        ConceptoNomina::create($this->validarDatosConcepto($request));

        return redirect()->route('conceptos-nomina.index')->with('exito', 'Concepto registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): RedirectResponse
    {
        return redirect()->route('conceptos-nomina.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        return view('conceptos_nomina.edit', [
            'concepto' => ConceptoNomina::findOrFail($id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $concepto = ConceptoNomina::findOrFail($id);
        $concepto->update($this->validarDatosConcepto($request, $concepto->id));

        return redirect()->route('conceptos-nomina.index')->with('exito', 'Concepto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        ConceptoNomina::findOrFail($id)->delete();

        return redirect()->route('conceptos-nomina.index')->with('exito', 'Concepto eliminado correctamente.');
    }

    private function validarDatosConcepto(Request $request, ?int $conceptoId = null): array
    {
        return $request->validate([
            'clave' => ['required', 'string', 'max:20', Rule::unique('concepto_nominas', 'clave')->ignore($conceptoId)],
            'nombre' => ['required', 'string', 'max:120'],
            'tipo' => ['required', Rule::in(['PERCEPCION', 'DEDUCCION', 'OTRO_PAGO'])],
            'gravado' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);
    }
}
