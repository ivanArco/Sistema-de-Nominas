<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AsistenciaController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->string('estado')->toString();
        $fecha = $request->string('fecha')->toString();

        $asistencias = Asistencia::query()
            ->with('empleado')
            ->when($estado !== '', fn ($q) => $q->where('estado', $estado))
            ->when($fecha !== '', fn ($q) => $q->whereDate('fecha', $fecha))
            ->tap(fn (Builder $consulta) => $this->aplicarAlcancePorAreaAsistencias($consulta, $request))
            ->orderByDesc('fecha')
            ->paginate(20)
            ->withQueryString();

        return view('asistencias.index', [
            'asistencias' => $asistencias,
            'filtros' => $request->only(['estado', 'fecha']),
        ]);
    }

    public function create(): View
    {
        $empleados = Empleado::query()->where('estatus', 'ACTIVO')->orderBy('nombre');
        $this->aplicarAlcancePorAreaEmpleados($empleados, request());

        return view('asistencias.create', [
            'empleados' => $empleados->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);
        $this->autorizarEmpleadoPorArea((int) $datos['empleado_id'], $request);
        Asistencia::create($datos);

        return redirect()->route('asistencias.index')->with('exito', 'Asistencia registrada correctamente.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('asistencias.edit', $id);
    }

    public function edit(string $id): View
    {
        $asistencia = Asistencia::findOrFail($id);
        $this->autorizarEmpleadoPorArea((int) $asistencia->empleado_id, request());

        $empleados = Empleado::query()->where('estatus', 'ACTIVO')->orderBy('nombre');
        $this->aplicarAlcancePorAreaEmpleados($empleados, request());

        return view('asistencias.edit', [
            'asistencia' => $asistencia,
            'empleados' => $empleados->get(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $asistencia = Asistencia::findOrFail($id);
        $this->autorizarEmpleadoPorArea((int) $asistencia->empleado_id, $request);

        $datos = $this->validarDatos($request, $asistencia->id);
        $this->autorizarEmpleadoPorArea((int) $datos['empleado_id'], $request);

        $asistencia->update($datos);

        return redirect()->route('asistencias.index')->with('exito', 'Asistencia actualizada correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $asistencia = Asistencia::findOrFail($id);
        $this->autorizarEmpleadoPorArea((int) $asistencia->empleado_id, request());
        $asistencia->delete();

        return redirect()->route('asistencias.index')->with('exito', 'Asistencia eliminada correctamente.');
    }

    private function validarDatos(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'fecha' => [
                'required',
                'date',
                Rule::unique('asistencias', 'fecha')
                    ->where(fn ($q) => $q->where('empleado_id', $request->input('empleado_id')))
                    ->ignore($id),
            ],
            'estado' => ['required', Rule::in(['ASISTENCIA', 'RETARDO', 'FALTA', 'PERMISO', 'VACACIONES'])],
            'horas_trabajadas' => ['required', 'numeric', 'min:0', 'max:24'],
            'origen' => ['nullable', 'string', 'max:40'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
        ]);
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

    private function aplicarAlcancePorAreaAsistencias(Builder $consulta, Request $request): void
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

        abort_if(!$autorizado, 403, 'Solo puedes gestionar asistencias de empleados de tu area.');
    }
}
