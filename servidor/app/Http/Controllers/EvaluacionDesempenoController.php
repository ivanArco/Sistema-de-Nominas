<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\EvaluacionDesempeno;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EvaluacionDesempenoController extends Controller
{
    public function index(Request $request): View
    {
        $query = EvaluacionDesempeno::query()->with(['empleado.departamento', 'evaluador']);

        if ($request->filled('periodo')) {
            $query->where('periodo', $request->string('periodo'));
        }

        $this->aplicarAlcancePorAreaEvaluaciones($query, $request);

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
        $empleados = Empleado::query()->orderBy('nombre');
        $this->aplicarAlcancePorAreaEmpleados($empleados, request());

        return view('evaluaciones.create', [
            'empleados' => $empleados->get(),
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

        $this->autorizarEmpleadoPorArea((int) $datos['empleado_id'], $request);

        $datos['evaluador_id'] = Auth::id();

        EvaluacionDesempeno::create($datos);

        return redirect()->route('evaluaciones.index')->with('exito', 'Evaluacion registrada correctamente.');
    }

    public function edit(string $id): View
    {
        $evaluacion = EvaluacionDesempeno::findOrFail($id);
        $this->autorizarEmpleadoPorArea((int) $evaluacion->empleado_id, request());

        $empleados = Empleado::query()->orderBy('nombre');
        $this->aplicarAlcancePorAreaEmpleados($empleados, request());

        return view('evaluaciones.edit', [
            'evaluacion' => $evaluacion,
            'empleados' => $empleados->get(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $evaluacion = EvaluacionDesempeno::findOrFail($id);
        $this->autorizarEmpleadoPorArea((int) $evaluacion->empleado_id, $request);

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

        $this->autorizarEmpleadoPorArea((int) $datos['empleado_id'], $request);

        $evaluacion->update($datos);

        return redirect()->route('evaluaciones.index')->with('exito', 'Evaluacion actualizada correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $evaluacion = EvaluacionDesempeno::findOrFail($id);
        $this->autorizarEmpleadoPorArea((int) $evaluacion->empleado_id, request());
        $evaluacion->delete();

        return redirect()->route('evaluaciones.index')->with('exito', 'Evaluacion eliminada correctamente.');
    }

    private function usuarioRestringidoPorArea(Request $request): bool
    {
        $usuario = $request->user();

        if (!$usuario instanceof User) {
            return false;
        }

        if ($usuario->rolNormalizado() === 'SUPERVISOR') {
            return true;
        }

        return $usuario->rolNormalizado() === 'JEFE_AREA' && !$usuario->esAdministrador();
    }

    private function resolverAreaGestion(Request $request): ?string
    {
        if (!$this->usuarioRestringidoPorArea($request)) {
            return null;
        }

        $area = trim((string) ($request->user()?->area_contratacion ?? ''));

        return $area !== '' ? $area : null;
    }

    private function aplicarAlcancePorAreaEvaluaciones(Builder $consulta, Request $request): void
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

    private function aplicarAlcancePorAreaEmpleados(Builder $consulta, Request $request): void
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
        abort_if($area === null, 403, 'Tu usuario no tiene area asignada para gestion.');

        $autorizado = Empleado::query()
            ->whereKey($empleadoId)
            ->whereHas('departamento', function (Builder $consulta) use ($area): void {
                $consulta->whereRaw('LOWER(nombre) = ?', [Str::lower($area)]);
            })
            ->exists();

        abort_if(!$autorizado, 403, 'Solo puedes gestionar evaluaciones de empleados de tu area.');
    }
}
