<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Solicitud;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    public function index(Request $request): View
    {
        $empleado = $this->resolverEmpleadoDelUsuario();

        if (!$empleado) {
            /** @var LengthAwarePaginator<int, Solicitud> $solicitudes */
            $solicitudes = Solicitud::query()
                ->whereRaw('1 = 0')
                ->paginate(12)
                ->withQueryString();

            return view('solicitudes.index', [
                'solicitudes' => $solicitudes,
                'filtros' => $request->only(['estatus']),
                'avisoSinEmpleado' => 'Tu cuenta no tiene empleado vinculado. Contacta a RH o a un supervisor para completar tu alta.',
            ]);
        }

        $estatus = $request->string('estatus')->toString();

        $solicitudes = Solicitud::query()
            ->where('empleado_id', $empleado->id)
            ->when($estatus !== '', fn ($consulta) => $consulta->where('estatus', $estatus))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('solicitudes.index', [
            'solicitudes' => $solicitudes,
            'filtros' => $request->only(['estatus']),
            'avisoSinEmpleado' => null,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if (!$this->resolverEmpleadoDelUsuario()) {
            return redirect()->route('solicitudes.index')->with('error', 'No puedes crear solicitudes hasta tener un empleado vinculado.');
        }

        return view('solicitudes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $empleado = $this->resolverEmpleadoDelUsuario();

        if (!$empleado) {
            return redirect()->route('solicitudes.index')->with('error', 'No puedes enviar solicitudes hasta tener un empleado vinculado.');
        }

        $datos = $request->validate([
            'tipo' => ['required', Rule::in(['VACACIONES', 'PERMISO'])],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'motivo' => ['nullable', 'string', 'max:1500'],
        ]);

        Solicitud::create([
            'empleado_id' => $empleado->id,
            'tipo' => $datos['tipo'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
            'motivo' => $datos['motivo'] ?? null,
            'estatus' => 'PENDIENTE',
        ]);

        return redirect()->route('solicitudes.index')->with('exito', 'Solicitud enviada correctamente.');
    }

    private function resolverEmpleadoDelUsuario(): ?Empleado
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return null;
        }

        return Empleado::query()
            ->where('curp', (string) $usuario->curp)
            ->orWhere('nss', (string) $usuario->numero_seguro_social)
            ->first();
    }
}
