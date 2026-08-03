<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\EmpleadoHistorial;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmpleadoController extends Controller
{
    public function index(Request $request): View
    {
        $texto = $request->string('texto')->trim()->toString();
        $estatus = $request->string('estatus')->toString();
        $areaSupervisor = $this->resolverAreaSupervisor($request);

        $consulta = Empleado::query()
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
            ->when($estatus !== '', fn ($consulta) => $consulta->where('estatus', $estatus));

        if ($this->esSupervisor($request)) {
            $this->aplicarFiltroAreaSupervisor($consulta, $areaSupervisor);
        }

        $empleados = $consulta
            ->orderBy('nombre')
            ->orderBy('ap_paterno')
            ->paginate(10)
            ->withQueryString();

        return view('empleados.index', [
            'empleados' => $empleados,
            'filtros' => $request->only(['texto', 'estatus']),
            'areaSupervisor' => $areaSupervisor,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $areaSupervisor = $this->resolverAreaSupervisor(request());

        $departamentos = Departamento::where('activo', true)
            ->when($this->esSupervisor(request()) && $areaSupervisor !== null, function ($consulta) use ($areaSupervisor) {
                $consulta->whereRaw('LOWER(nombre) = ?', [Str::lower($areaSupervisor)]);
            })
            ->orderBy('nombre')
            ->get();

        return view('empleados.create', [
            'departamentos' => $departamentos,
            'puestos' => Puesto::where('activo', true)->orderBy('nombre')->get(),
            'areaSupervisor' => $areaSupervisor,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $datosEmpleado = $this->validarDatosEmpleado($request);

        $this->autorizarDepartamentoSupervisor((int) $datosEmpleado['depto_id'], $request);

        $datosUsuario = $this->validarDatosUsuarioAcceso($request, $datosEmpleado);
        $credencialesGeneradas = [
            'nombre_usuario' => '',
            'contrasena_temporal' => '',
        ];

        DB::transaction(function () use ($datosEmpleado, $datosUsuario, &$credencialesGeneradas): void {
            $empleado = Empleado::create($datosEmpleado);
            $this->registrarHistorial($empleado, 'ALTA');
            $credencialesGeneradas = $this->crearUsuarioAccesoAutomatico($datosEmpleado, $datosUsuario['rol']);
        });

        $mensaje = 'Empleado y usuario de acceso registrados correctamente. '
            .'Usuario: '.$credencialesGeneradas['nombre_usuario']
            .' | Contrasena temporal: '.$credencialesGeneradas['contrasena_temporal'];

        return redirect()->route('empleados.index')->with('exito', $mensaje);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): RedirectResponse
    {
        $empleado = Empleado::with('departamento')->findOrFail($id);
        $this->autorizarSupervisorSobreEmpleado($empleado, request());

        return redirect()->route('empleados.edit', $empleado->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $empleado = Empleado::with('departamento')->findOrFail($id);
        $this->autorizarSupervisorSobreEmpleado($empleado, request());

        $areaSupervisor = $this->resolverAreaSupervisor(request());

        $departamentos = Departamento::where('activo', true)
            ->when($this->esSupervisor(request()) && $areaSupervisor !== null, function ($consulta) use ($areaSupervisor) {
                $consulta->whereRaw('LOWER(nombre) = ?', [Str::lower($areaSupervisor)]);
            })
            ->orderBy('nombre')
            ->get();

        return view('empleados.edit', [
            'empleado' => $empleado,
            'departamentos' => $departamentos,
            'puestos' => Puesto::where('activo', true)->orderBy('nombre')->get(),
            'areaSupervisor' => $areaSupervisor,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $empleado = Empleado::findOrFail($id);
        $this->autorizarSupervisorSobreEmpleado($empleado, $request);

        $datos = $this->validarDatosEmpleado($request, $empleado->id);
        $this->validarRestriccionSalarioSecretaria($datos, $empleado, $request);
        $this->autorizarDepartamentoSupervisor((int) $datos['depto_id'], $request);

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
        $empleado = Empleado::with('departamento')->findOrFail($id);
        $this->autorizarSupervisorSobreEmpleado($empleado, request());

        $empleado->delete();

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

    private function validarDatosUsuarioAcceso(Request $request, array $datosEmpleado): array
    {
        $datos = $request->validate([
            'usuario_rol' => ['required', Rule::in(User::rolesDisponibles())],
        ], [
            'usuario_rol.required' => 'Selecciona el rol del usuario de acceso.',
        ]);

        $mensajes = [];

        if (User::query()->where('curp', $datosEmpleado['curp'])->exists()) {
            $mensajes['curp'] = 'Ya existe un usuario de acceso con el CURP del empleado.';
        }

        if (User::query()->where('numero_seguro_social', $datosEmpleado['nss'])->exists()) {
            $mensajes['nss'] = 'Ya existe un usuario de acceso con el NSS del empleado.';
        }

        if (!empty($mensajes)) {
            throw ValidationException::withMessages($mensajes);
        }

        return [
            'rol' => $datos['usuario_rol'],
        ];
    }

    private function crearUsuarioAccesoAutomatico(array $datosEmpleado, string $rol): array
    {
        $nombreUsuario = $this->generarNombreUsuarioUnico($datosEmpleado);
        $correoAcceso = $this->generarCorreoAccesoUnico($datosEmpleado, $nombreUsuario);
        $contrasenaTemporal = Str::password(12, true, true, true, false);
        $rolNormalizado = User::normalizarRol($rol);

        $areaContratacion = null;
        if ($rolNormalizado === 'SUPERVISOR') {
            $areaContratacion = Departamento::query()
                ->whereKey((int) $datosEmpleado['depto_id'])
                ->value('nombre');
        }

        User::create([
            'nombre_usuario' => $nombreUsuario,
            'name' => $nombreUsuario,
            'email' => $correoAcceso,
            'password' => $contrasenaTemporal,
            'nombre' => $datosEmpleado['nombre'],
            'apellido_paterno' => $datosEmpleado['ap_paterno'],
            'apellido_materno' => $datosEmpleado['ap_materno'] ?? null,
            'curp' => $datosEmpleado['curp'],
            'telefono_contacto_1' => $datosEmpleado['telefono'] ?? null,
            'telefono_contacto_2' => null,
            'fecha_contratacion' => $datosEmpleado['f_ingreso'],
            'area_contratacion' => $areaContratacion,
            'numero_seguro_social' => $datosEmpleado['nss'],
            'fecha_alta_servicio_salud' => null,
            'direccion' => null,
            'colonia' => null,
            'codigo_postal' => null,
            'ciudad' => null,
            'estado' => null,
            'rol' => $rolNormalizado,
            'rol_id' => User::resolverRolId($rolNormalizado),
            'activo' => true,
        ]);

        return [
            'nombre_usuario' => $nombreUsuario,
            'correo_electronico' => $correoAcceso,
            'contrasena_temporal' => $contrasenaTemporal,
        ];
    }

    private function generarNombreUsuarioUnico(array $datosEmpleado): string
    {
        $base = Str::of(($datosEmpleado['nombre'] ?? '').'.'.($datosEmpleado['ap_paterno'] ?? ''))
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9\.]+/', '')
            ->trim('.')
            ->value();

        if ($base === '') {
            $base = 'emp'.$datosEmpleado['num_empleado'];
        }

        $usuario = $base;
        $contador = 1;

        while (User::query()->where('nombre_usuario', $usuario)->exists()) {
            $usuario = $base.$contador;
            $contador++;
        }

        return $usuario;
    }

    private function generarCorreoAccesoUnico(array $datosEmpleado, string $nombreUsuario): string
    {
        $correoEmpleado = strtolower(trim((string) ($datosEmpleado['correo'] ?? '')));
        if ($correoEmpleado !== '' && !User::query()->where('email', $correoEmpleado)->exists()) {
            return $correoEmpleado;
        }

        $correoBase = $nombreUsuario.'@nomina.local';
        $correo = $correoBase;
        $contador = 1;

        while (User::query()->where('email', $correo)->exists()) {
            $correo = $nombreUsuario.$contador.'@nomina.local';
            $contador++;
        }

        return $correo;
    }

    private function esSupervisor(Request $request): bool
    {
        $usuario = $request->user();

        return $usuario instanceof User && $usuario->rolNormalizado() === 'SUPERVISOR';
    }

    private function esSecretaria(Request $request): bool
    {
        $usuario = $request->user();

        return $usuario instanceof User && $usuario->rolNormalizado() === 'SECRETARIA';
    }

    private function validarRestriccionSalarioSecretaria(array $datos, Empleado $empleado, Request $request): void
    {
        if (!$this->esSecretaria($request)) {
            return;
        }

        $cambioSalario = (float) $empleado->sal_dia !== (float) ($datos['sal_dia'] ?? $empleado->sal_dia)
            || (float) $empleado->sal_int !== (float) ($datos['sal_int'] ?? $empleado->sal_int);

        if ($cambioSalario) {
            throw ValidationException::withMessages([
                'sal_dia' => 'La secretaria no tiene permiso para modificar salarios.',
                'sal_int' => 'La secretaria no tiene permiso para modificar salarios.',
            ]);
        }
    }

    private function resolverAreaSupervisor(Request $request): ?string
    {
        if (!$this->esSupervisor($request)) {
            return null;
        }

        $area = trim((string) ($request->user()?->area_contratacion ?? ''));

        return $area !== '' ? $area : null;
    }

    private function aplicarFiltroAreaSupervisor(Builder $consulta, ?string $areaSupervisor): void
    {
        if ($areaSupervisor === null) {
            $consulta->whereRaw('1 = 0');

            return;
        }

        $consulta->whereHas('departamento', function (Builder $subconsulta) use ($areaSupervisor): void {
            $subconsulta->whereRaw('LOWER(nombre) = ?', [Str::lower($areaSupervisor)]);
        });
    }

    private function autorizarSupervisorSobreEmpleado(Empleado $empleado, Request $request): void
    {
        if (!$this->esSupervisor($request)) {
            return;
        }

        $areaSupervisor = $this->resolverAreaSupervisor($request);
        abort_if($areaSupervisor === null, 403, 'Tu usuario supervisor no tiene area asignada.');

        $nombreDepartamento = $empleado->relationLoaded('departamento')
            ? (string) ($empleado->departamento->nombre ?? '')
            : (string) $empleado->departamento()->value('nombre');

        abort_if(Str::lower(trim($nombreDepartamento)) !== Str::lower($areaSupervisor), 403, 'Solo puedes gestionar empleados de tu area.');
    }

    private function autorizarDepartamentoSupervisor(int $deptoId, Request $request): void
    {
        if (!$this->esSupervisor($request)) {
            return;
        }

        $areaSupervisor = $this->resolverAreaSupervisor($request);
        abort_if($areaSupervisor === null, 403, 'Tu usuario supervisor no tiene area asignada.');

        $nombreDepartamento = (string) Departamento::query()->whereKey($deptoId)->value('nombre');

        abort_if($nombreDepartamento === '', 422, 'El departamento seleccionado no existe.');
        abort_if(Str::lower(trim($nombreDepartamento)) !== Str::lower($areaSupervisor), 403, 'Solo puedes asignar empleados a tu area.');
    }
}
