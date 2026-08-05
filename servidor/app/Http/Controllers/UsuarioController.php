<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\EmpleadoHistorial;
use App\Models\Puesto;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    /**
     * Roles que requieren un registro de empleado (datos de nomina) vinculado.
     * Ajusta esta lista si cambia la regla de negocio.
     */
    private const ROLES_CON_EMPLEADO = ['EMPLEADO', 'VENDEDOR', 'CONTADOR', 'SECRETARIA'];

    /**
     * Muestra el listado de usuarios con filtros de consulta.
     * Acceso: usuarios.consultar o usuarios.gestionar (ver middleware en routes/web.php).
     */
    public function index(Request $request): View
    {
        $consultaUsuarios = $this->construirConsultaUsuarios($request)
            ->orderBy('nombre')
            ->orderBy('apellido_paterno');

        $usuarios = $consultaUsuarios->paginate(10)->withQueryString();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'filtros' => $request->only(['texto', 'rol', 'activo', 'estado', 'fecha_desde', 'fecha_hasta']),
            'puedeEliminar' => $request->user()?->tienePermiso('usuarios.eliminar') ?? false,
            'puedeGestionar' => $request->user()?->tienePermiso('usuarios.gestionar') ?? false,
        ]);
    }

    /**
     * Muestra el formulario de alta.
     * Acceso: usuarios.gestionar (Contador, con solo usuarios.consultar, no llega aqui).
     */
    public function create(): View
    {
        $areaGestion = $this->resolverAreaGestion(request());

        return view('usuarios.create', [
            'departamentos' => Departamento::where('activo', true)
                ->when($this->usuarioRestringidoPorArea(request()) && $areaGestion !== null, function ($consulta) use ($areaGestion) {
                    $consulta->whereRaw('LOWER(nombre) = ?', [Str::lower($areaGestion)]);
                })
                ->orderBy('nombre')
                ->get(),
            'puestos' => Puesto::where('activo', true)->orderBy('nombre')->get(),
            'rolesConEmpleado' => self::ROLES_CON_EMPLEADO,
            'empleadoVinculado' => null,
        ]);
    }

    /**
     * Guarda un usuario nuevo. Si el rol lo requiere, crea tambien su empleado vinculado.
     */
    public function store(Request $request): RedirectResponse
    {
        $datosValidados = $this->validarDatosUsuario($request, null, true);
        $rolNormalizado = User::normalizarRol($datosValidados['rol']);
        $requiereEmpleado = in_array($rolNormalizado, self::ROLES_CON_EMPLEADO, true);

        $datosEmpleadoVinculado = $requiereEmpleado
            ? $this->validarDatosEmpleadoVinculado($request)
            : null;

        if (is_array($datosEmpleadoVinculado)) {
            $this->autorizarDepartamentoGestion((int) $datosEmpleadoVinculado['depto_id'], $request);
        }

        if ($this->usuarioRestringidoPorArea($request)) {
            $areaGestion = $this->resolverAreaGestion($request);
            abort_if($areaGestion === null, 403, 'Tu usuario no tiene area asignada para gestion.');
            $datosValidados['area_contratacion'] = $areaGestion;
        }

        // Regla solicitada: la contrasena inicial del nuevo usuario es su CURP.
        $datosValidados['contrasena'] = (string) $datosValidados['curp'];

        DB::transaction(function () use ($datosValidados, $datosEmpleadoVinculado, $requiereEmpleado, $rolNormalizado): void {
            User::create([
                'nombre_usuario' => $datosValidados['nombre_usuario'],
                'name' => $datosValidados['nombre_usuario'],
                'email' => $datosValidados['correo_electronico'],
                'password' => Hash::make($datosValidados['contrasena']),
                'nombre' => $datosValidados['nombre'],
                'apellido_paterno' => $datosValidados['apellido_paterno'],
                'apellido_materno' => $datosValidados['apellido_materno'] ?? null,
                'curp' => $datosValidados['curp'],
                'telefono_contacto_1' => $datosValidados['telefono_contacto_1'],
                'telefono_contacto_2' => $datosValidados['telefono_contacto_2'] ?? null,
                'fecha_contratacion' => $datosValidados['fecha_contratacion'],
                'area_contratacion' => $datosValidados['area_contratacion'],
                'numero_seguro_social' => $datosValidados['numero_seguro_social'],
                'fecha_alta_servicio_salud' => $datosValidados['fecha_alta_servicio_salud'] ?? null,
                'direccion' => $datosValidados['direccion'],
                'colonia' => $datosValidados['colonia'],
                'codigo_postal' => $datosValidados['codigo_postal'],
                'ciudad' => $datosValidados['ciudad'],
                'estado' => $datosValidados['estado'],
                'rol' => $rolNormalizado,
                'rol_id' => User::resolverRolId($rolNormalizado),
                'activo' => isset($datosValidados['activo']) ? (bool) $datosValidados['activo'] : true,
            ]);

            if ($requiereEmpleado && is_array($datosEmpleadoVinculado)) {
                $empleado = Empleado::create([
                    'num_empleado' => $datosEmpleadoVinculado['num_empleado'],
                    'nombre' => $datosValidados['nombre'],
                    'ap_paterno' => $datosValidados['apellido_paterno'],
                    'ap_materno' => $datosValidados['apellido_materno'] ?? null,
                    'curp' => $datosValidados['curp'],
                    'rfc' => $datosEmpleadoVinculado['rfc'],
                    'nss' => $datosValidados['numero_seguro_social'],
                    'correo' => $datosValidados['correo_electronico'],
                    'telefono' => $datosValidados['telefono_contacto_1'],
                    'f_ingreso' => $datosValidados['fecha_contratacion'],
                    'f_baja' => null,
                    'tipo_cont' => $datosEmpleadoVinculado['tipo_cont'],
                    'jornada' => $datosEmpleadoVinculado['jornada'],
                    'tipo_pago' => $datosEmpleadoVinculado['tipo_pago'],
                    'sal_dia' => $datosEmpleadoVinculado['sal_dia'],
                    'sal_int' => $datosEmpleadoVinculado['sal_int'],
                    'depto_id' => $datosEmpleadoVinculado['depto_id'],
                    'puesto_id' => $datosEmpleadoVinculado['puesto_id'],
                    'porcentaje_infonavit' => $datosEmpleadoVinculado['porcentaje_infonavit'] ?? 0,
                    'porcentaje_afore' => $datosEmpleadoVinculado['porcentaje_afore'] ?? 1.125,
                    'usa_fondo_ahorro' => isset($datosEmpleadoVinculado['usa_fondo_ahorro']) ? (bool) $datosEmpleadoVinculado['usa_fondo_ahorro'] : false,
                    'porcentaje_fondo_ahorro' => $datosEmpleadoVinculado['porcentaje_fondo_ahorro'] ?? 0,
                    'semanas_cotizadas' => $datosEmpleadoVinculado['semanas_cotizadas'] ?? 0,
                    'fondo_retiro_acumulado' => $datosEmpleadoVinculado['fondo_retiro_acumulado'] ?? 0,
                    'estatus' => 'ACTIVO',
                ]);

                EmpleadoHistorial::create([
                    'empleado_id' => $empleado->id,
                    'fecha_movimiento' => now()->toDateString(),
                    'tipo_movimiento' => 'ALTA',
                    'salario_diario' => (float) $empleado->sal_dia,
                    'puesto_id' => $empleado->puesto_id,
                    'semanas_cotizadas' => (float) ($empleado->semanas_cotizadas ?? 0),
                    'fondo_retiro_acumulado' => (float) ($empleado->fondo_retiro_acumulado ?? 0),
                    'observaciones' => 'Alta de empleado generada automaticamente por rol '.$rolNormalizado.' desde modulo de usuarios.',
                ]);
            }
        });

        $mensaje = 'Usuario registrado correctamente.';
        if ($requiereEmpleado) {
            $mensaje .= ' Empleado vinculado creado automaticamente segun el rol asignado.';
        }

        $mensaje .= ' Contrasena inicial asignada: CURP del usuario.';

        return redirect()->route('usuarios.index')->with('exito', $mensaje);
    }

    /**
     * Muestra el detalle de un usuario (solo lectura).
     * Acceso: usuarios.consultar o usuarios.gestionar.
     */
    public function show(string $id): View
    {
        $usuario = User::findOrFail($id);
        $this->autorizarUsuarioPorArea($usuario, request());
        $empleadoVinculado = Empleado::where('curp', $usuario->curp)->first();

        return view('usuarios.show', [
            'usuario' => $usuario,
            'empleadoVinculado' => $empleadoVinculado,
        ]);
    }

    /**
     * Muestra formulario de edicion.
     * Acceso: usuarios.gestionar.
     */
    public function edit(string $id): View
    {
        $usuario = User::findOrFail($id);
        $this->autorizarUsuarioPorArea($usuario, request());
        $empleadoVinculado = Empleado::where('curp', $usuario->curp)->first();
        $areaGestion = $this->resolverAreaGestion(request());

        return view('usuarios.edit', [
            'usuario' => $usuario,
            'departamentos' => Departamento::where('activo', true)
                ->when($this->usuarioRestringidoPorArea(request()) && $areaGestion !== null, function ($consulta) use ($areaGestion) {
                    $consulta->whereRaw('LOWER(nombre) = ?', [Str::lower($areaGestion)]);
                })
                ->orderBy('nombre')
                ->get(),
            'puestos' => Puesto::where('activo', true)->orderBy('nombre')->get(),
            'rolesConEmpleado' => self::ROLES_CON_EMPLEADO,
            'empleadoVinculado' => $empleadoVinculado,
        ]);
    }

    /**
     * Actualiza los datos de un usuario, sincronizando su empleado vinculado
     * segun el rol resultante (alta, actualizacion o baja automatica).
     * Acceso: usuarios.gestionar.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $usuario = User::findOrFail($id);
        $this->autorizarUsuarioPorArea($usuario, $request);
        $curpAnterior = $usuario->curp;

        $datosValidados = $this->validarDatosUsuario($request, $usuario->id);
        $rolNormalizado = User::normalizarRol($datosValidados['rol']);
        $requiereEmpleado = in_array($rolNormalizado, self::ROLES_CON_EMPLEADO, true);

        $empleadoExistente = Empleado::where('curp', $curpAnterior)->first();

        $datosEmpleadoVinculado = $requiereEmpleado
            ? $this->validarDatosEmpleadoVinculado($request, $empleadoExistente?->id)
            : null;

        if (is_array($datosEmpleadoVinculado)) {
            $this->autorizarDepartamentoGestion((int) $datosEmpleadoVinculado['depto_id'], $request);
        }

        if ($this->usuarioRestringidoPorArea($request)) {
            $areaGestion = $this->resolverAreaGestion($request);
            abort_if($areaGestion === null, 403, 'Tu usuario no tiene area asignada para gestion.');
            $datosValidados['area_contratacion'] = $areaGestion;
        }

        DB::transaction(function () use (
            $usuario,
            $datosValidados,
            $rolNormalizado,
            $requiereEmpleado,
            $datosEmpleadoVinculado,
            $empleadoExistente
        ): void {
            $usuario->fill([
                'nombre_usuario' => $datosValidados['nombre_usuario'],
                'name' => $datosValidados['nombre_usuario'],
                'email' => $datosValidados['correo_electronico'],
                'nombre' => $datosValidados['nombre'],
                'apellido_paterno' => $datosValidados['apellido_paterno'],
                'apellido_materno' => $datosValidados['apellido_materno'] ?? null,
                'curp' => $datosValidados['curp'],
                'telefono_contacto_1' => $datosValidados['telefono_contacto_1'],
                'telefono_contacto_2' => $datosValidados['telefono_contacto_2'] ?? null,
                'fecha_contratacion' => $datosValidados['fecha_contratacion'],
                'area_contratacion' => $datosValidados['area_contratacion'],
                'numero_seguro_social' => $datosValidados['numero_seguro_social'],
                'fecha_alta_servicio_salud' => $datosValidados['fecha_alta_servicio_salud'] ?? null,
                'direccion' => $datosValidados['direccion'],
                'colonia' => $datosValidados['colonia'],
                'codigo_postal' => $datosValidados['codigo_postal'],
                'ciudad' => $datosValidados['ciudad'],
                'estado' => $datosValidados['estado'],
                'rol' => $rolNormalizado,
                'rol_id' => User::resolverRolId($rolNormalizado),
                'activo' => isset($datosValidados['activo']) ? (bool) $datosValidados['activo'] : false,
            ]);

            if (!empty($datosValidados['contrasena'])) {
                $usuario->password = Hash::make($datosValidados['contrasena']);
            }

            $usuario->save();

            // Caso 1: el rol requiere empleado y ya existia -> actualizar sus datos.
            if ($requiereEmpleado && $empleadoExistente && is_array($datosEmpleadoVinculado)) {
                $empleadoExistente->fill([
                    'nombre' => $datosValidados['nombre'],
                    'ap_paterno' => $datosValidados['apellido_paterno'],
                    'ap_materno' => $datosValidados['apellido_materno'] ?? null,
                    'curp' => $datosValidados['curp'],
                    'rfc' => $datosEmpleadoVinculado['rfc'],
                    'nss' => $datosValidados['numero_seguro_social'],
                    'correo' => $datosValidados['correo_electronico'],
                    'telefono' => $datosValidados['telefono_contacto_1'],
                    'tipo_cont' => $datosEmpleadoVinculado['tipo_cont'],
                    'jornada' => $datosEmpleadoVinculado['jornada'],
                    'tipo_pago' => $datosEmpleadoVinculado['tipo_pago'],
                    'sal_dia' => $datosEmpleadoVinculado['sal_dia'],
                    'sal_int' => $datosEmpleadoVinculado['sal_int'],
                    'depto_id' => $datosEmpleadoVinculado['depto_id'],
                    'puesto_id' => $datosEmpleadoVinculado['puesto_id'],
                    'porcentaje_infonavit' => $datosEmpleadoVinculado['porcentaje_infonavit'] ?? 0,
                    'porcentaje_afore' => $datosEmpleadoVinculado['porcentaje_afore'] ?? 1.125,
                    'usa_fondo_ahorro' => isset($datosEmpleadoVinculado['usa_fondo_ahorro']) ? (bool) $datosEmpleadoVinculado['usa_fondo_ahorro'] : false,
                    'porcentaje_fondo_ahorro' => $datosEmpleadoVinculado['porcentaje_fondo_ahorro'] ?? 0,
                    'semanas_cotizadas' => $datosEmpleadoVinculado['semanas_cotizadas'] ?? 0,
                    'fondo_retiro_acumulado' => $datosEmpleadoVinculado['fondo_retiro_acumulado'] ?? 0,
                    'estatus' => 'ACTIVO',
                    'f_baja' => null,
                ]);
                $empleadoExistente->save();
            }

            // Caso 2: el rol ahora requiere empleado pero todavia no existia -> crearlo.
            if ($requiereEmpleado && !$empleadoExistente && is_array($datosEmpleadoVinculado)) {
                $empleadoNuevo = Empleado::create([
                    'num_empleado' => $datosEmpleadoVinculado['num_empleado'],
                    'nombre' => $datosValidados['nombre'],
                    'ap_paterno' => $datosValidados['apellido_paterno'],
                    'ap_materno' => $datosValidados['apellido_materno'] ?? null,
                    'curp' => $datosValidados['curp'],
                    'rfc' => $datosEmpleadoVinculado['rfc'],
                    'nss' => $datosValidados['numero_seguro_social'],
                    'correo' => $datosValidados['correo_electronico'],
                    'telefono' => $datosValidados['telefono_contacto_1'],
                    'f_ingreso' => $datosValidados['fecha_contratacion'],
                    'f_baja' => null,
                    'tipo_cont' => $datosEmpleadoVinculado['tipo_cont'],
                    'jornada' => $datosEmpleadoVinculado['jornada'],
                    'tipo_pago' => $datosEmpleadoVinculado['tipo_pago'],
                    'sal_dia' => $datosEmpleadoVinculado['sal_dia'],
                    'sal_int' => $datosEmpleadoVinculado['sal_int'],
                    'depto_id' => $datosEmpleadoVinculado['depto_id'],
                    'puesto_id' => $datosEmpleadoVinculado['puesto_id'],
                    'porcentaje_infonavit' => $datosEmpleadoVinculado['porcentaje_infonavit'] ?? 0,
                    'porcentaje_afore' => $datosEmpleadoVinculado['porcentaje_afore'] ?? 1.125,
                    'usa_fondo_ahorro' => isset($datosEmpleadoVinculado['usa_fondo_ahorro']) ? (bool) $datosEmpleadoVinculado['usa_fondo_ahorro'] : false,
                    'porcentaje_fondo_ahorro' => $datosEmpleadoVinculado['porcentaje_fondo_ahorro'] ?? 0,
                    'semanas_cotizadas' => $datosEmpleadoVinculado['semanas_cotizadas'] ?? 0,
                    'fondo_retiro_acumulado' => $datosEmpleadoVinculado['fondo_retiro_acumulado'] ?? 0,
                    'estatus' => 'ACTIVO',
                ]);

                EmpleadoHistorial::create([
                    'empleado_id' => $empleadoNuevo->id,
                    'fecha_movimiento' => now()->toDateString(),
                    'tipo_movimiento' => 'ALTA',
                    'salario_diario' => (float) $empleadoNuevo->sal_dia,
                    'puesto_id' => $empleadoNuevo->puesto_id,
                    'semanas_cotizadas' => (float) ($empleadoNuevo->semanas_cotizadas ?? 0),
                    'fondo_retiro_acumulado' => (float) ($empleadoNuevo->fondo_retiro_acumulado ?? 0),
                    'observaciones' => 'Alta de empleado generada automaticamente por cambio de rol a '.$rolNormalizado.'.',
                ]);
            }

            // Caso 3: ya no requiere empleado pero tenia uno activo -> dar de baja.
            if (!$requiereEmpleado && $empleadoExistente && $empleadoExistente->estatus === 'ACTIVO') {
                $empleadoExistente->fill([
                    'estatus' => 'BAJA',
                    'f_baja' => now()->toDateString(),
                ]);
                $empleadoExistente->save();

                EmpleadoHistorial::create([
                    'empleado_id' => $empleadoExistente->id,
                    'fecha_movimiento' => now()->toDateString(),
                    'tipo_movimiento' => 'BAJA',
                    'salario_diario' => (float) $empleadoExistente->sal_dia,
                    'puesto_id' => $empleadoExistente->puesto_id,
                    'semanas_cotizadas' => (float) ($empleadoExistente->semanas_cotizadas ?? 0),
                    'fondo_retiro_acumulado' => (float) ($empleadoExistente->fondo_retiro_acumulado ?? 0),
                    'observaciones' => 'Baja automatica de empleado por cambio de rol a '.$rolNormalizado.'.',
                ]);
            }
        });

        return redirect()->route('usuarios.index')->with('exito', 'Usuario actualizado correctamente.');
    }

    /**
     * Elimina un usuario.
     * Acceso: usuarios.eliminar -- middleware de ruta ya garantiza que solo Jefe de Area
     * (via su comodin '*' en PERMISOS_POR_ROL) llega aqui. Ningun otro rol tiene
     * 'usuarios.eliminar' asignado explicitamente.
     */
    public function destroy(string $id): RedirectResponse
    {
        $usuario = User::findOrFail($id);
        $this->autorizarUsuarioPorArea($usuario, request());
        $usuario->delete();

        return redirect()->route('usuarios.index')->with('exito', 'Usuario eliminado correctamente.');
    }

    /**
     * Genera un reporte PDF segun los filtros de busqueda activos.
     * Acceso: usuarios.consultar o usuarios.gestionar.
     */
    public function reportePdf(Request $request)
    {
        $usuarios = $this->construirConsultaUsuarios($request)
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->get();

        $pdf = Pdf::loadView('usuarios.reporte_pdf', [
            'usuarios' => $usuarios,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('reporte_usuarios.pdf');
    }

    /**
     * Construye la consulta con criterios mas comunes para el listado.
     */
    private function construirConsultaUsuarios(Request $request)
    {
        $texto = $request->string('texto')->trim()->toString();
        $rol = $request->string('rol')->toString();
        $activo = $request->string('activo')->toString();
        $estado = $request->string('estado')->trim()->toString();
        $fechaDesde = $request->string('fecha_desde')->toString();
        $fechaHasta = $request->string('fecha_hasta')->toString();

        return User::query()
            ->when($texto !== '', function ($consulta) use ($texto) {
                $consulta->where(function ($subconsulta) use ($texto) {
                    $subconsulta->where('nombre_usuario', 'like', "%{$texto}%")
                        ->orWhere('nombre', 'like', "%{$texto}%")
                        ->orWhere('apellido_paterno', 'like', "%{$texto}%")
                        ->orWhere('apellido_materno', 'like', "%{$texto}%")
                        ->orWhere('email', 'like', "%{$texto}%")
                        ->orWhere('curp', 'like', "%{$texto}%")
                        ->orWhere('numero_seguro_social', 'like', "%{$texto}%");
                });
            })
            ->when($rol !== '', fn ($consulta) => $consulta->where('rol', $rol))
            ->when($activo !== '', fn ($consulta) => $consulta->where('activo', $activo === '1'))
            ->when($estado !== '', fn ($consulta) => $consulta->where('estado', 'like', "%{$estado}%"))
            ->when($fechaDesde !== '', fn ($consulta) => $consulta->whereDate('fecha_contratacion', '>=', $fechaDesde))
            ->when($fechaHasta !== '', fn ($consulta) => $consulta->whereDate('fecha_contratacion', '<=', $fechaHasta))
            ->when($this->resolverAreaGestion($request) !== null, function ($consulta) use ($request) {
                $areaGestion = $this->resolverAreaGestion($request);

                $consulta->where(function ($subconsulta) use ($areaGestion) {
                    $subconsulta->whereRaw('LOWER(area_contratacion) = ?', [Str::lower((string) $areaGestion)])
                        ->orWhereExists(function ($enlaceEmpleado) use ($areaGestion) {
                            $enlaceEmpleado->selectRaw('1')
                                ->from('empleados')
                                ->join('departamentos', 'departamentos.id', '=', 'empleados.depto_id')
                                ->where(function ($llaves) {
                                    $llaves->whereColumn('empleados.curp', 'users.curp')
                                        ->orWhereColumn('empleados.nss', 'users.numero_seguro_social');
                                })
                                ->whereRaw('LOWER(departamentos.nombre) = ?', [Str::lower((string) $areaGestion)]);
                        });
                });
            });
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

    private function autorizarDepartamentoGestion(int $deptoId, Request $request): void
    {
        if (!$this->usuarioRestringidoPorArea($request)) {
            return;
        }

        $area = $this->resolverAreaGestion($request);
        abort_if($area === null, 403, 'Tu usuario no tiene area asignada para gestion.');

        $nombreDepartamento = (string) Departamento::query()->whereKey($deptoId)->value('nombre');
        abort_if($nombreDepartamento === '', 422, 'El departamento seleccionado no existe.');
        abort_if(Str::lower(trim($nombreDepartamento)) !== Str::lower($area), 403, 'Solo puedes gestionar usuarios y empleados de tu area.');
    }

    private function autorizarUsuarioPorArea(User $usuario, Request $request): void
    {
        if (!$this->usuarioRestringidoPorArea($request)) {
            return;
        }

        $area = $this->resolverAreaGestion($request);
        abort_if($area === null, 403, 'Tu usuario no tiene area asignada para gestion.');

        $mismoAreaUsuario = Str::lower(trim((string) ($usuario->area_contratacion ?? ''))) === Str::lower($area);

        $mismoAreaEmpleado = Empleado::query()
            ->where(function ($enlace) use ($usuario) {
                $enlace->where('curp', (string) $usuario->curp)
                    ->orWhere('nss', (string) $usuario->numero_seguro_social);
            })
            ->whereHas('departamento', function ($consulta) use ($area) {
                $consulta->whereRaw('LOWER(nombre) = ?', [Str::lower($area)]);
            })
            ->exists();

        abort_if(!$mismoAreaUsuario && !$mismoAreaEmpleado, 403, 'Solo puedes gestionar usuarios y empleados de tu area.');
    }

    /**
     * Valida datos de alta/edicion de usuario.
     */
    private function validarDatosUsuario(Request $request, ?int $usuarioId = null, bool $generarContrasenaAutomatica = false): array
    {
        if ($usuarioId) {
            $reglasContrasena = ['nullable', 'string', 'min:8', 'confirmed'];
        } elseif ($generarContrasenaAutomatica) {
            $reglasContrasena = ['nullable', 'string', 'min:8', 'confirmed'];
        } else {
            $reglasContrasena = ['required', 'string', 'min:8', 'confirmed'];
        }

        $datos = $request->validate([
            'nombre_usuario' => ['required', 'string', 'max:50', Rule::unique('users', 'nombre_usuario')->ignore($usuarioId)],
            'contrasena' => $reglasContrasena,
            'nombre' => ['required', 'string', 'max:80'],
            'apellido_paterno' => ['required', 'string', 'max:80'],
            'apellido_materno' => ['nullable', 'string', 'max:80'],
            'curp' => ['required', 'string', 'size:18', Rule::unique('users', 'curp')->ignore($usuarioId)],
            'correo_electronico' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($usuarioId)],
            'telefono_contacto_1' => ['required', 'string', 'max:20'],
            'telefono_contacto_2' => ['nullable', 'string', 'max:20'],
            'fecha_contratacion' => ['required', 'date'],
            'area_contratacion' => ['required', 'string', 'max:100'],
            'numero_seguro_social' => ['required', 'string', 'max:20', Rule::unique('users', 'numero_seguro_social')->ignore($usuarioId)],
            'fecha_alta_servicio_salud' => ['nullable', 'date'],
            'direccion' => ['required', 'string', 'max:200'],
            'colonia' => ['required', 'string', 'max:120'],
            'codigo_postal' => ['required', 'string', 'max:10'],
            'ciudad' => ['required', 'string', 'max:120'],
            'estado' => ['required', 'string', 'max:120'],
            'rol' => ['required', Rule::in(User::rolesDisponibles())],
            'activo' => ['nullable', 'boolean'],
        ], [
            'contrasena.confirmed' => 'La confirmacion de contrasena no coincide.',
        ]);

        if (User::normalizarRol((string) $datos['rol']) === 'SUPERVISOR') {
            $areaSupervisor = trim((string) $datos['area_contratacion']);

            $departamentoExiste = Departamento::query()
                ->whereRaw('LOWER(nombre) = ?', [Str::lower($areaSupervisor)])
                ->exists();

            if (!$departamentoExiste) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'area_contratacion' => 'Para rol SUPERVISOR, el area debe coincidir con un departamento activo del sistema.',
                ]);
            }

            $nombreDepartamento = (string) Departamento::query()
                ->whereRaw('LOWER(nombre) = ?', [Str::lower($areaSupervisor)])
                ->value('nombre');

            $datos['area_contratacion'] = $nombreDepartamento;
        }

        return $datos;
    }

    /**
     * Valida los datos del bloque "empleado vinculado".
     * $empleadoId permite ignorar el propio registro en las reglas unique (edicion).
     */
    private function validarDatosEmpleadoVinculado(Request $request, ?int $empleadoId = null): array
    {
        $datos = $request->validate([
            'num_empleado' => ['required', 'string', 'max:30', Rule::unique('empleados', 'num_empleado')->ignore($empleadoId)],
            'rfc_empleado' => ['required', 'string', 'size:13', Rule::unique('empleados', 'rfc')->ignore($empleadoId)],
            'depto_id_empleado' => ['required', 'integer', 'exists:departamentos,id'],
            'puesto_id_empleado' => ['required', 'integer', 'exists:puestos,id'],
            'tipo_cont_empleado' => ['required', 'string', 'max:50'],
            'jornada_empleado' => ['required', 'string', 'max:50'],
            'tipo_pago_empleado' => ['required', Rule::in(['SEMANAL', 'QUINCENAL', 'MENSUAL'])],
            'sal_dia_empleado' => ['required', 'numeric', 'min:0'],
            'sal_int_empleado' => ['nullable', 'numeric', 'min:0'],
            'porcentaje_infonavit_empleado' => ['nullable', 'numeric', 'min:0', 'max:30'],
            'porcentaje_afore_empleado' => ['nullable', 'numeric', 'min:0', 'max:30'],
            'usa_fondo_ahorro_empleado' => ['nullable', 'boolean'],
            'porcentaje_fondo_ahorro_empleado' => ['nullable', 'numeric', 'min:0', 'max:30'],
            'semanas_cotizadas_empleado' => ['nullable', 'numeric', 'min:0'],
            'fondo_retiro_acumulado_empleado' => ['nullable', 'numeric', 'min:0'],
        ]);

        return [
            'num_empleado' => $datos['num_empleado'],
            'rfc' => strtoupper($datos['rfc_empleado']),
            'depto_id' => (int) $datos['depto_id_empleado'],
            'puesto_id' => (int) $datos['puesto_id_empleado'],
            'tipo_cont' => $datos['tipo_cont_empleado'],
            'jornada' => $datos['jornada_empleado'],
            'tipo_pago' => $datos['tipo_pago_empleado'],
            'sal_dia' => (float) $datos['sal_dia_empleado'],
            'sal_int' => isset($datos['sal_int_empleado']) ? (float) $datos['sal_int_empleado'] : null,
            'porcentaje_infonavit' => (float) ($datos['porcentaje_infonavit_empleado'] ?? 0),
            'porcentaje_afore' => (float) ($datos['porcentaje_afore_empleado'] ?? 1.125),
            'usa_fondo_ahorro' => isset($datos['usa_fondo_ahorro_empleado']) ? (bool) $datos['usa_fondo_ahorro_empleado'] : false,
            'porcentaje_fondo_ahorro' => (float) ($datos['porcentaje_fondo_ahorro_empleado'] ?? 0),
            'semanas_cotizadas' => (float) ($datos['semanas_cotizadas_empleado'] ?? 0),
            'fondo_retiro_acumulado' => (float) ($datos['fondo_retiro_acumulado_empleado'] ?? 0),
        ];
    }
}