<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(Request $request): View
    {
        $estatus = $request->string('estatus')->toString();
        $empleadoTexto = $request->string('empleado')->trim()->toString();
        $vendedorId = (int) $request->integer('vendedor_id');
        $vendedores = $this->vendedoresDisponibles();

        $ventas = Venta::query()
            ->with(['empleado.departamento'])
            ->when($estatus !== '', fn ($q) => $q->where('estatus', $estatus))
            ->when($vendedorId > 0, fn ($q) => $q->where('empleado_id', $vendedorId))
            ->when($empleadoTexto !== '', function ($q) use ($empleadoTexto) {
                $q->whereHas('empleado', function ($sub) use ($empleadoTexto) {
                    $sub->where('num_empleado', 'like', "%{$empleadoTexto}%")
                        ->orWhere('nombre', 'like', "%{$empleadoTexto}%")
                        ->orWhere('ap_paterno', 'like', "%{$empleadoTexto}%");
                });
            });

        $this->aplicarAlcancePorRol($ventas);

        $ventas = $ventas->orderByDesc('fecha_venta')->paginate(20)->withQueryString();

        return view('ventas.index', [
            'ventas' => $ventas,
            'filtros' => $request->only(['estatus', 'empleado', 'vendedor_id']),
            'vendedores' => $vendedores,
        ]);
    }

    public function create(): View
    {
        return view('ventas.create', [
            'empleados' => $this->vendedoresDisponibles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);
        $datos['folio'] = $this->folioUnico($datos['folio'] ?? null);
        $datos['comision_calculada'] = $this->calcularComision($datos);
        $datos['bono_estatus'] = ((float) ($datos['bono_desempeno'] ?? 0)) > 0 ? 'PENDIENTE' : 'APROBADO';
        $datos['bono_autorizado_por'] = null;
        $datos['bono_autorizado_fecha'] = null;
        $datos['bono_autorizado_comentario'] = null;

        Venta::create($datos);

        return redirect()->route('ventas.index')->with('exito', 'Venta registrada correctamente.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('ventas.edit', $id);
    }

    public function edit(string $id): View
    {
        $venta = Venta::findOrFail($id);
        $this->validarAccesoRegistro($venta);

        return view('ventas.edit', [
            'venta' => $venta,
            'empleados' => $this->vendedoresDisponibles(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $venta = Venta::findOrFail($id);
        $this->validarAccesoRegistro($venta);

        $datos = $this->validarDatos($request, $venta->id);
        $datos['folio'] = $this->folioUnico($datos['folio'] ?? null, $venta->id);
        $datos['comision_calculada'] = $this->calcularComision($datos);

        $nuevoBono = (float) ($datos['bono_desempeno'] ?? 0);
        if ($nuevoBono <= 0) {
            $datos['bono_estatus'] = 'APROBADO';
            $datos['bono_autorizado_por'] = null;
            $datos['bono_autorizado_fecha'] = null;
            $datos['bono_autorizado_comentario'] = null;
        } elseif ((float) $venta->bono_desempeno !== $nuevoBono) {
            $datos['bono_estatus'] = 'PENDIENTE';
            $datos['bono_autorizado_por'] = null;
            $datos['bono_autorizado_fecha'] = null;
            $datos['bono_autorizado_comentario'] = null;
        }

        $venta->update($datos);

        return redirect()->route('ventas.index')->with('exito', 'Venta actualizada correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $venta = Venta::findOrFail($id);
        $this->validarAccesoRegistro($venta);
        $venta->delete();

        return redirect()->route('ventas.index')->with('exito', 'Venta eliminada correctamente.');
    }

    public function autorizarBono(Request $request, string $id): RedirectResponse
    {
        $venta = Venta::findOrFail($id);
        /** @var User|null $usuario */
        $usuario = Auth::user();
        abort_if(!$usuario || !$usuario->tienePermiso('bonos.autorizar'), 403);

        if ($usuario->rolNormalizado() === 'JEFE_AREA') {
            $areaJefe = strtolower(trim((string) $usuario->area_contratacion));
            $areaVenta = strtolower(trim((string) optional($venta->empleado?->departamento)->nombre));
            abort_if($areaJefe !== '' && $areaJefe !== $areaVenta, 403);
        }

        $datos = $request->validate([
            'bono_estatus' => ['required', Rule::in(['APROBADO', 'RECHAZADO'])],
            'bono_autorizado_comentario' => ['nullable', 'string', 'max:1500'],
        ]);

        if ((float) $venta->bono_desempeno <= 0) {
            return back()->with('error', 'La venta no tiene bono por autorizar.');
        }

        $venta->update([
            'bono_estatus' => $datos['bono_estatus'],
            'bono_autorizado_por' => Auth::id(),
            'bono_autorizado_fecha' => now(),
            'bono_autorizado_comentario' => $datos['bono_autorizado_comentario'] ?? null,
        ]);

        return back()->with('exito', 'Estatus de bono actualizado correctamente.');
    }

    private function validarDatos(Request $request, ?int $id = null): array
    {
        $reglasEmpleado = ['required', 'integer', 'exists:empleados,id'];

        /** @var User|null $usuario */
        $usuario = Auth::user();

        if ($usuario?->tienePermiso('ventas.gestionar')) {
            $idsVendedores = $this->vendedoresDisponibles()->pluck('id')->all();
            $reglasEmpleado[] = Rule::in($idsVendedores);
        }

        if ($usuario?->tienePermiso('ventas.propias') && !$usuario?->tienePermiso('ventas.gestionar')) {
            $empleado = $this->resolverEmpleadoDelUsuario();
            abort_if(!$empleado, 404, 'No hay empleado vinculado para este usuario.');
            $reglasEmpleado[] = Rule::in([$empleado->id]);
        }

        return $request->validate([
            'empleado_id' => $reglasEmpleado,
            'folio' => ['nullable', 'string', 'max:50', Rule::unique('ventas', 'folio')->ignore($id)],
            'fecha_venta' => ['required', 'date'],
            'cliente_nombre' => ['nullable', 'string', 'max:120'],
            'monto_bruto' => ['required', 'numeric', 'min:0'],
            'porcentaje_comision' => ['required', 'numeric', 'min:0', 'max:100'],
            'bono_desempeno' => ['nullable', 'numeric', 'min:0'],
            'estatus' => ['required', Rule::in(['REGISTRADA', 'CERRADA', 'CANCELADA'])],
            'observaciones' => ['nullable', 'string', 'max:1500'],
        ]);
    }

    private function calcularComision(array $datos): float
    {
        $monto = (float) ($datos['monto_bruto'] ?? 0);
        $porcentaje = (float) ($datos['porcentaje_comision'] ?? 0);

        return round($monto * ($porcentaje / 100), 2);
    }

    private function folioUnico(?string $folio, ?int $ignoreId = null): string
    {
        $folioBase = trim((string) $folio);
        if ($folioBase === '') {
            $folioBase = 'VTA-'.now()->format('Ymd-His');
        }

        $folioFinal = Str::upper($folioBase);
        $contador = 1;

        while (Venta::query()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('folio', $folioFinal)
            ->exists()) {
            $folioFinal = Str::upper($folioBase).'-'.$contador;
            $contador++;
        }

        return $folioFinal;
    }

    private function aplicarAlcancePorRol($consulta): void
    {
        /** @var User|null $usuario */
        $usuario = Auth::user();
        if (!$usuario) {
            return;
        }

        if ($usuario->tienePermiso('ventas.gestionar')) {
            if ($this->usuarioRestringidoPorArea($usuario)) {
                $deptoId = Empleado::query()
                    ->where('curp', (string) $usuario->curp)
                    ->orWhere('nss', (string) $usuario->numero_seguro_social)
                    ->value('depto_id');

                if ($deptoId) {
                    $consulta->whereHas('empleado', fn ($sub) => $sub->where('depto_id', $deptoId));
                    return;
                }

                $consulta->whereRaw('1 = 0');
            }

            return;
        }

        if ($usuario->tienePermiso('ventas.propias')) {
            $empleado = $this->resolverEmpleadoDelUsuario();
            $consulta->where('empleado_id', $empleado?->id ?? 0);
        }
    }

    private function resolverEmpleadoDelUsuario(): ?Empleado
    {
        /** @var User|null $usuario */
        $usuario = Auth::user();
        if (!$usuario) {
            return null;
        }

        return Empleado::query()
            ->where('curp', (string) $usuario->curp)
            ->orWhere('nss', (string) $usuario->numero_seguro_social)
            ->first();
    }

    private function validarAccesoRegistro(Venta $venta): void
    {
        /** @var User|null $usuario */
        $usuario = Auth::user();
        if (!$usuario) {
            abort(403);
        }

        if ($usuario->tienePermiso('ventas.gestionar')) {
            if (!$this->usuarioRestringidoPorArea($usuario)) {
                return;
            }

            $deptoId = Empleado::query()
                ->where('curp', (string) $usuario->curp)
                ->orWhere('nss', (string) $usuario->numero_seguro_social)
                ->value('depto_id');

            abort_if(!$deptoId || (int) $venta->empleado?->depto_id !== (int) $deptoId, 403);
            return;
        }

        if ($usuario->tienePermiso('ventas.propias')) {
            $empleado = $this->resolverEmpleadoDelUsuario();
            abort_if(!$empleado || (int) $venta->empleado_id !== (int) $empleado->id, 403);
            return;
        }

        abort(403);
    }

    private function empleadosDisponibles()
    {
        $consulta = Empleado::query()->where('estatus', 'ACTIVO')->orderBy('nombre');
        /** @var User|null $usuario */
        $usuario = Auth::user();

        if ($usuario?->tienePermiso('ventas.gestionar')) {
            if ($usuario && $this->usuarioRestringidoPorArea($usuario)) {
                $deptoId = Empleado::query()
                    ->where('curp', (string) $usuario->curp)
                    ->orWhere('nss', (string) $usuario->numero_seguro_social)
                    ->value('depto_id');

                if ($deptoId) {
                    $consulta->where('depto_id', $deptoId);
                } else {
                    $consulta->whereRaw('1 = 0');
                }
            }

            return $consulta->get();
        }

        $empleado = $this->resolverEmpleadoDelUsuario();
        if ($empleado) {
            $consulta->whereKey($empleado->id);
        } else {
            $consulta->whereRaw('1 = 0');
        }

        return $consulta->get();
    }

    private function vendedoresDisponibles()
    {
        $consulta = Empleado::query()
            ->where('estatus', 'ACTIVO')
            ->whereExists(function ($subconsulta) {
                $subconsulta->selectRaw('1')
                    ->from('users')
                    ->where(function ($enlace) {
                        $enlace->whereColumn('users.curp', 'empleados.curp')
                            ->orWhereColumn('users.numero_seguro_social', 'empleados.nss');
                    })
                    ->where('users.rol', 'VENDEDOR');
            })
            ->orderBy('nombre')
            ->orderBy('ap_paterno');

        /** @var User|null $usuario */
        $usuario = Auth::user();

        if ($usuario?->tienePermiso('ventas.gestionar')) {
            if ($usuario && $this->usuarioRestringidoPorArea($usuario)) {
                $deptoId = Empleado::query()
                    ->where('curp', (string) $usuario->curp)
                    ->orWhere('nss', (string) $usuario->numero_seguro_social)
                    ->value('depto_id');

                if ($deptoId) {
                    $consulta->where('depto_id', $deptoId);
                } else {
                    $consulta->whereRaw('1 = 0');
                }
            }

            return $consulta->get();
        }

        if ($usuario?->tienePermiso('ventas.propias')) {
            $empleado = $this->resolverEmpleadoDelUsuario();

            if ($empleado) {
                $consulta->whereKey($empleado->id);
            } else {
                $consulta->whereRaw('1 = 0');
            }
        }

        return $consulta->get();
    }

    private function usuarioRestringidoPorArea(User $usuario): bool
    {
        $rol = $usuario->rolNormalizado();

        if ($rol === 'SUPERVISOR') {
            return true;
        }

        return $rol === 'JEFE_AREA' && !$usuario->esAdministrador();
    }
}
