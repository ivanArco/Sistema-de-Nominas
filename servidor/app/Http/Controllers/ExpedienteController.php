<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Expediente;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExpedienteController extends Controller
{
    public function index(Request $request): View
    {
        $texto = $request->string('empleado')->trim()->toString();

        $expedientes = Expediente::query()
            ->with(['empleado', 'cargador'])
            ->when($texto !== '', function ($q) use ($texto) {
                $q->whereHas('empleado', function ($sub) use ($texto) {
                    $sub->where('num_empleado', 'like', "%{$texto}%")
                        ->orWhere('nombre', 'like', "%{$texto}%")
                        ->orWhere('ap_paterno', 'like', "%{$texto}%");
                });
            })
            ->tap(fn (Builder $consulta) => $this->aplicarAlcancePorAreaExpedientes($consulta, $request))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('expedientes.index', [
            'expedientes' => $expedientes,
            'filtros' => $request->only(['empleado']),
        ]);
    }

    public function create(): View
    {
        $empleados = Empleado::query()->where('estatus', 'ACTIVO')->orderBy('nombre');
        $this->aplicarAlcancePorAreaEmpleados($empleados, request());

        return view('expedientes.create', [
            'empleados' => $empleados->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'tipo_documento' => ['required', 'string', 'max:80'],
            'fecha_documento' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
            'archivo' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        $this->autorizarEmpleadoPorArea((int) $datos['empleado_id'], $request);

        $archivo = $request->file('archivo');
        $ruta = $archivo->store('expedientes', 'local');

        Expediente::create([
            'empleado_id' => $datos['empleado_id'],
            'tipo_documento' => $datos['tipo_documento'],
            'nombre_archivo' => $archivo->getClientOriginalName(),
            'ruta_archivo' => $ruta,
            'fecha_documento' => $datos['fecha_documento'] ?? null,
            'observaciones' => $datos['observaciones'] ?? null,
            'cargado_por' => Auth::id(),
        ]);

        return redirect()->route('expedientes.index')->with('exito', 'Documento cargado correctamente.');
    }

    public function show(string $id)
    {
        $expediente = Expediente::findOrFail($id);
        $this->autorizarEmpleadoPorArea((int) $expediente->empleado_id, request());

        if (!Storage::disk('local')->exists($expediente->ruta_archivo)) {
            return back()->with('error', 'Archivo no disponible en almacenamiento.');
        }

        return response()->download(
            Storage::disk('local')->path($expediente->ruta_archivo),
            $expediente->nombre_archivo
        );
    }

    public function destroy(string $id): RedirectResponse
    {
        $expediente = Expediente::findOrFail($id);
        $this->autorizarEmpleadoPorArea((int) $expediente->empleado_id, request());
        if (Storage::disk('local')->exists($expediente->ruta_archivo)) {
            Storage::disk('local')->delete($expediente->ruta_archivo);
        }

        $expediente->delete();

        return redirect()->route('expedientes.index')->with('exito', 'Documento eliminado correctamente.');
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

    private function aplicarAlcancePorAreaExpedientes(Builder $consulta, Request $request): void
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

        abort_if(!$autorizado, 403, 'Solo puedes gestionar expedientes de empleados de tu area.');
    }
}
