<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\EvaluacionDesempeno;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EvaluacionDesempenoController extends Controller
{
    public function index(Request $request): View
    {
        $query = EvaluacionDesempeno::query()->with(['empleado.departamento', 'evaluador']);

        if ($request->filled('periodo')) {
            $query->where('periodo', $request->string('periodo'));
        }

        $evaluaciones = $query->orderByDesc('fecha_evaluacion')->paginate(20)->withQueryString();

        return view('evaluaciones.index', [
            'evaluaciones' => $evaluaciones,
            'filtros' => [
                'periodo' => (string) $request->string('periodo'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('evaluaciones.create', [
            'empleados' => Empleado::query()->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'periodo' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'fecha_evaluacion' => ['required', 'date'],
            'puntaje' => ['required', 'integer', 'min:0', 'max:100'],
            'resultado' => ['required', 'in:EXCELENTE,BUENO,REGULAR,BAJO'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
            'plan_accion' => ['nullable', 'string', 'max:1500'],
            'estatus' => ['required', 'in:ABIERTA,CERRADA'],
        ]);

        $datos['evaluador_id'] = Auth::id();

        EvaluacionDesempeno::create($datos);

        return redirect()->route('evaluaciones.index')->with('exito', 'Evaluacion registrada correctamente.');
    }

    public function edit(string $id): View
    {
        return view('evaluaciones.edit', [
            'evaluacion' => EvaluacionDesempeno::findOrFail($id),
            'empleados' => Empleado::query()->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $evaluacion = EvaluacionDesempeno::findOrFail($id);

        $datos = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'periodo' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'fecha_evaluacion' => ['required', 'date'],
            'puntaje' => ['required', 'integer', 'min:0', 'max:100'],
            'resultado' => ['required', 'in:EXCELENTE,BUENO,REGULAR,BAJO'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
            'plan_accion' => ['nullable', 'string', 'max:1500'],
            'estatus' => ['required', 'in:ABIERTA,CERRADA'],
        ]);

        $evaluacion->update($datos);

        return redirect()->route('evaluaciones.index')->with('exito', 'Evaluacion actualizada correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        EvaluacionDesempeno::findOrFail($id)->delete();

        return redirect()->route('evaluaciones.index')->with('exito', 'Evaluacion eliminada correctamente.');
    }
}
