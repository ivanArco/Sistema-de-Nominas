<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Venta;
use App\Models\VentaMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VentaMetaController extends Controller
{
    public function index(Request $request): View
    {
        $query = VentaMeta::query()->with(['empleado.departamento']);

        if ($request->filled('periodo')) {
            $query->where('periodo', $request->string('periodo'));
        }

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
        return view('ventas_metas.create', [
            'empleados' => Empleado::query()->orderBy('nombre')->get(),
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

        VentaMeta::create($datos);

        return redirect()->route('ventas.metas.index')->with('exito', 'Meta de ventas creada correctamente.');
    }

    public function edit(string $id): View
    {
        return view('ventas_metas.edit', [
            'meta' => VentaMeta::findOrFail($id),
            'empleados' => Empleado::query()->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $meta = VentaMeta::findOrFail($id);

        $datos = $request->validate([
            'empleado_id' => ['required', 'exists:empleados,id'],
            'periodo' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'monto_meta' => ['required', 'numeric', 'min:0'],
            'comision_objetivo' => ['nullable', 'numeric', 'min:0'],
            'bono_objetivo' => ['nullable', 'numeric', 'min:0'],
            'estatus' => ['required', 'in:ACTIVA,CERRADA,CANCELADA'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
        ]);

        $meta->update($datos);

        return redirect()->route('ventas.metas.index')->with('exito', 'Meta de ventas actualizada correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        VentaMeta::findOrFail($id)->delete();

        return redirect()->route('ventas.metas.index')->with('exito', 'Meta de ventas eliminada correctamente.');
    }
}
