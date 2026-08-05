<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VentaMetaController extends Controller
{
    public function index(Request $request): View
    {
        $query = VentaMeta::query()->with(['empleado.departamento']);

        if ($request->filled('periodo')) {
            $query->where('periodo', $request->string('periodo'));
        }

        $this->aplicarAlcancePorAreaMetas($query, $request);

        $metas = $query->orderByDesc('periodo')->orderByDesc('id')->paginate(20)->withQueryString();

        $avancePorMeta = [];
        foreach ($metas as $meta) {
            $montoReal = (float) Venta::query()
                ->where('empleado_id', $meta->empleado_id)
                ->whereRaw("DATE_FORMAT(fecha_venta, '%Y-%m') = ?", [$meta->periodo])
                ->sum('monto_bruto');

            $avancePorMeta[$meta->id] = [
                'monto_real' => $montoReal,
                'porcentaje' => $meta->monto_meta > 0 ? round(($montoReal / (float) $meta->monto_meta) * 100, 2) : 0,
            ];
        }

        return view('ventas_metas.index', [
            'metas' => $metas,
            'filtros' => [
                'periodo' => (string) $request->string('periodo'),
            ],
            'avancePorMeta' => $avancePorMeta,
        ]);
    }

    public function create(): View
    {
        $empleados = Empleado::query()->orderBy('nombre');
        $this->aplicarAlcancePorAreaEmpleados($empleados, request());

        return view('ventas_metas.create', [
            'empleados' => $empleados->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'periodo' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'monto_meta' => ['required', 'numeric', 'min:0'],
            'comision_objetivo' => ['nullable', 'numeric', 'min:0'],
            'bono_objetivo' => ['nullable', 'numeric', 'min:0'],
            'estatus' => ['required', 'in:ACTIVA,CERRADA,CANCELADA'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
        ]);

        $this->autorizarEmpleadoPorArea((int) $datos['empleado_id'], $request);

        VentaMeta::create($datos);

        return redirect()->route('ventas.metas.index')->with('exito', 'Meta de ventas creada correctamente.');
    }

    public function edit(string $id): View
    {
        $meta = VentaMeta::findOrFail($id);
        $this->autorizarEmpleadoPorArea((int) $meta->empleado_id, request());

        $empleados = Empleado::query()->orderBy('nombre');
        $this->aplicarAlcancePorAreaEmpleados($empleados, request());

        return view('ventas_metas.edit', [
            'meta' => $meta,
            'empleados' => $empleados->get(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $meta = VentaMeta::findOrFail($id);
        $this->autorizarEmpleadoPorArea((int) $meta->empleado_id, $request);

        $datos = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'periodo' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'monto_meta' => ['required', 'numeric', 'min:0'],
            'comision_objetivo' => ['nullable', 'numeric', 'min:0'],
            'bono_objetivo' => ['nullable', 'numeric', 'min:0'],
            'estatus' => ['required', 'in:ACTIVA,CERRADA,CANCELADA'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
        ]);

        $this->autorizarEmpleadoPorArea((int) $datos['empleado_id'], $request);

        $meta->update($datos);

        return redirect()->route('ventas.metas.index')->with('exito', 'Meta de ventas actualizada correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $meta = VentaMeta::findOrFail($id);
        $this->autorizarEmpleadoPorArea((int) $meta->empleado_id, request());
        $meta->delete();

        return redirect()->route('ventas.metas.index')->with('exito', 'Meta de ventas eliminada correctamente.');
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

    private function aplicarAlcancePorAreaMetas(Builder $consulta, Request $request): void
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

        abort_if(!$autorizado, 403, 'Solo puedes gestionar metas de ventas de empleados de tu area.');
    }
}
