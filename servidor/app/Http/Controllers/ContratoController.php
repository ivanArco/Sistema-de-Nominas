<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Empleado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContratoController extends Controller
{
    public function index(Request $request): View
    {
        $query = Contrato::query()->with('empleado.departamento');

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->string('estatus'));
        }

        $contratos = $query->orderByDesc('fecha_inicio')->paginate(20)->withQueryString();

        return view('contratos.index', [
            'contratos' => $contratos,
            'filtros' => [
                'estatus' => (string) $request->string('estatus'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('contratos.create', [
            'empleados' => Empleado::query()->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'tipo' => ['required', 'in:INDEFINIDO,TEMPORAL,PRACTICAS,OUTSOURCING'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'sueldo_mensual' => ['required', 'numeric', 'min:0'],
            'jornada' => ['required', 'in:COMPLETA,MEDIO_TIEMPO,NOCTURNA,MIXTA'],
            'estatus' => ['required', 'in:ACTIVO,VENCIDO,CANCELADO'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
        ]);

        Contrato::create($datos);

        return redirect()->route('contratos.index')->with('exito', 'Contrato registrado correctamente.');
    }

    public function edit(string $id): View
    {
        return view('contratos.edit', [
            'contrato' => Contrato::findOrFail($id),
            'empleados' => Empleado::query()->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $contrato = Contrato::findOrFail($id);

        $datos = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'tipo' => ['required', 'in:INDEFINIDO,TEMPORAL,PRACTICAS,OUTSOURCING'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'sueldo_mensual' => ['required', 'numeric', 'min:0'],
            'jornada' => ['required', 'in:COMPLETA,MEDIO_TIEMPO,NOCTURNA,MIXTA'],
            'estatus' => ['required', 'in:ACTIVO,VENCIDO,CANCELADO'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
        ]);

        $contrato->update($datos);

        return redirect()->route('contratos.index')->with('exito', 'Contrato actualizado correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        Contrato::findOrFail($id)->delete();

        return redirect()->route('contratos.index')->with('exito', 'Contrato eliminado correctamente.');
    }
}
