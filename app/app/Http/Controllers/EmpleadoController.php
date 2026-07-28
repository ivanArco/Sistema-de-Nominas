<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\EmpleadoHistorial;
use App\Models\Puesto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmpleadoController extends Controller
{
    public function index(Request $request): View
    {
        $texto = $request->string('texto')->trim()->toString();
        $estatus = $request->string('estatus')->toString();

        $empleados = Empleado::query()
            ->with(['departamento', 'puesto'])
            ->when($texto !== '', function ($consulta) use ($texto) {
                $consulta->where(function ($subconsulta) use ($texto) {
                    $subconsulta->where('num_empleado', 'like', "%{$texto}%")
                        ->orWhere('nombre', 'like', "%{$texto}%")
                        ->orWhere('ap_paterno', 'like', "%{$texto}%")
                        ->orWhere('curp', 'like', "%{$texto}%")
                        ->orWhere('rfc', 'like', "%{$texto}%")
                        ->orWhere('nss', 'like', "%{$texto}%");
                });
            })
            ->when($estatus !== '', fn ($consulta) => $consulta->where('estatus', $estatus))
            ->orderBy('nombre')
            ->orderBy('ap_paterno')
            ->paginate(10)
            ->withQueryString();

        return view('empleados.index', [
            'empleados' => $empleados,
            'filtros' => $request->only(['texto', 'estatus']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('empleados.create', [
            'departamentos' => Departamento::where('activo', true)->orderBy('nombre')->get(),
            'puestos' => Puesto::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatosEmpleado($request);
        $empleado = Empleado::create($datos);
        $this->registrarHistorial($empleado, 'ALTA');

        return redirect()->route('empleados.index')->with('exito', 'Empleado registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): RedirectResponse
    {
        return redirect()->route('empleados.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        return view('empleados.edit', [
            'empleado' => Empleado::findOrFail($id),
            'departamentos' => Departamento::where('activo', true)->orderBy('nombre')->get(),
            'puestos' => Puesto::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $empleado = Empleado::findOrFail($id);
        $datos = $this->validarDatosEmpleado($request, $empleado->id);

        $huboCambioLaboral = (float) $empleado->sal_dia !== (float) $datos['sal_dia']
            || (int) $empleado->puesto_id !== (int) $datos['puesto_id']
            || (string) $empleado->estatus !== (string) $datos['estatus']
            || (float) ($empleado->semanas_cotizadas ?? 0) !== (float) ($datos['semanas_cotizadas'] ?? 0)
            || (float) ($empleado->fondo_retiro_acumulado ?? 0) !== (float) ($datos['fondo_retiro_acumulado'] ?? 0);

        $empleado->update($datos);

        if ($huboCambioLaboral) {
            $this->registrarHistorial($empleado->fresh(), 'ACTUALIZACION');
        }

        return redirect()->route('empleados.index')->with('exito', 'Empleado actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        Empleado::findOrFail($id)->delete();

        return redirect()->route('empleados.index')->with('exito', 'Empleado eliminado correctamente.');
    }

    private function validarDatosEmpleado(Request $request, ?int $empleadoId = null): array
    {
        return $request->validate([
            'num_empleado' => ['required', 'string', 'max:30', Rule::unique('empleados', 'num_empleado')->ignore($empleadoId)],
            'nombre' => ['required', 'string', 'max:80'],
            'ap_paterno' => ['required', 'string', 'max:80'],
            'ap_materno' => ['nullable', 'string', 'max:80'],
            'curp' => ['required', 'string', 'size:18', Rule::unique('empleados', 'curp')->ignore($empleadoId)],
            'rfc' => ['required', 'string', 'size:13', Rule::unique('empleados', 'rfc')->ignore($empleadoId)],
            'nss' => ['required', 'string', 'max:20', Rule::unique('empleados', 'nss')->ignore($empleadoId)],
            'correo' => ['nullable', 'email', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'f_ingreso' => ['required', 'date'],
            'f_baja' => ['nullable', 'date', 'after_or_equal:f_ingreso'],
            'tipo_cont' => ['required', 'string', 'max:50'],
            'jornada' => ['required', 'string', 'max:50'],
            'tipo_pago' => ['required', Rule::in(['SEMANAL', 'QUINCENAL', 'MENSUAL'])],
            'sal_dia' => ['required', 'numeric', 'min:0'],
            'sal_int' => ['required', 'numeric', 'min:0'],
            'depto_id' => ['required', 'integer', 'exists:departamentos,id'],
            'puesto_id' => ['required', 'integer', 'exists:puestos,id'],
            'porcentaje_infonavit' => ['nullable', 'numeric', 'min:0', 'max:30'],
            'porcentaje_afore' => ['nullable', 'numeric', 'min:0', 'max:30'],
            'usa_fondo_ahorro' => ['nullable', 'boolean'],
            'porcentaje_fondo_ahorro' => ['nullable', 'numeric', 'min:0', 'max:30'],
            'semanas_cotizadas' => ['nullable', 'numeric', 'min:0'],
            'fondo_retiro_acumulado' => ['nullable', 'numeric', 'min:0'],
            'estatus' => ['required', Rule::in(['ACTIVO', 'BAJA'])],
        ]);
    }

    private function registrarHistorial(Empleado $empleado, string $tipoMovimiento): void
    {
        EmpleadoHistorial::create([
            'empleado_id' => $empleado->id,
            'fecha_movimiento' => now()->toDateString(),
            'tipo_movimiento' => $tipoMovimiento,
            'salario_diario' => (float) $empleado->sal_dia,
            'puesto_id' => $empleado->puesto_id,
            'semanas_cotizadas' => (float) ($empleado->semanas_cotizadas ?? 0),
            'fondo_retiro_acumulado' => (float) ($empleado->fondo_retiro_acumulado ?? 0),
            'observaciones' => 'Registro automatico de historial laboral.',
        ]);
    }
}
