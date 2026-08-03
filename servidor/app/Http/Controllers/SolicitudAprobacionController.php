<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SolicitudAprobacionController extends Controller
{
    public function index(Request $request): View
    {
        $estatus = $request->string('estatus')->toString();
        $tipo = $request->string('tipo')->toString();

        $consulta = Solicitud::query()->with(['empleado.departamento', 'revisor']);
        $this->aplicarAlcancePorRol($consulta);

        /** @var User|null $usuario */
        $usuario = Auth::user();

        if ($usuario?->rolNormalizado() === 'SUPERVISOR') {
            $consulta->where('etapa_supervisor_estatus', 'PENDIENTE');
        }

        if ($usuario?->tienePermiso('solicitudes.aprobar.final')) {
            $consulta->where('etapa_supervisor_estatus', 'APROBADA')
                ->where('etapa_jefe_estatus', 'PENDIENTE');
        }

        $solicitudes = $consulta
            ->when($estatus !== '', fn ($q) => $q->where('estatus', $estatus))
            ->when($tipo !== '', fn ($q) => $q->where('tipo', $tipo))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('solicitudes.aprobacion_index', [
            'solicitudes' => $solicitudes,
            'filtros' => $request->only(['estatus', 'tipo']),
        ]);
    }

    public function update(Request $request, Solicitud $solicitud): RedirectResponse
    {
        $datos = $request->validate([
            'estatus' => ['required', Rule::in(['APROBADA', 'RECHAZADA'])],
            'comentario_revision' => ['nullable', 'string', 'max:1500'],
        ]);

        /** @var User|null $usuario */
        $usuario = Auth::user();
        abort_if(!$usuario, 403);

        $consulta = Solicitud::query()->whereKey($solicitud->id);
        $this->aplicarAlcancePorRol($consulta);
        $solicitudAlcance = $consulta->firstOrFail();

        if ($solicitudAlcance->estatus !== 'PENDIENTE') {
            return back()->with('error', 'Solo se pueden revisar solicitudes pendientes.');
        }

        if ($usuario->rolNormalizado() === 'SUPERVISOR') {
            if ($solicitudAlcance->etapa_supervisor_estatus !== 'PENDIENTE') {
                return back()->with('error', 'La solicitud ya fue revisada por supervisor.');
            }

            $solicitudAlcance->update([
                'etapa_supervisor_estatus' => $datos['estatus'],
                'aprobado_supervisor_por' => $usuario->id,
                'fecha_aprobacion_supervisor' => now(),
                'comentario_supervisor' => $datos['comentario_revision'] ?? null,
                'estatus' => $datos['estatus'] === 'RECHAZADA' ? 'RECHAZADA' : 'PENDIENTE',
                'revisado_por' => $usuario->id,
                'fecha_revision' => now(),
                'comentario_revision' => $datos['comentario_revision'] ?? null,
            ]);

            return back()->with('exito', 'Revision de supervisor registrada correctamente.');
        }

        if ($usuario->tienePermiso('solicitudes.aprobar.final')) {
            if ($solicitudAlcance->etapa_supervisor_estatus !== 'APROBADA') {
                return back()->with('error', 'Solo puedes revisar solicitudes ya aprobadas por supervisor.');
            }

            if ($solicitudAlcance->etapa_jefe_estatus !== 'PENDIENTE') {
                return back()->with('error', 'La solicitud ya fue revisada por Jefe de Area.');
            }

            $solicitudAlcance->update([
                'etapa_jefe_estatus' => $datos['estatus'],
                'aprobado_jefe_por' => $usuario->id,
                'fecha_aprobacion_jefe' => now(),
                'comentario_jefe' => $datos['comentario_revision'] ?? null,
                'estatus' => $datos['estatus'] === 'APROBADA' ? 'APROBADA' : 'RECHAZADA',
                'revisado_por' => $usuario->id,
                'fecha_revision' => now(),
                'comentario_revision' => $datos['comentario_revision'] ?? null,
            ]);

            return back()->with('exito', 'Aprobacion final registrada correctamente.');
        }

        abort(403);

    }

    private function aplicarAlcancePorRol($consulta): void
    {
        /** @var User|null $usuario */
        $usuario = Auth::user();

        if (!$usuario || $usuario->rolNormalizado() !== 'SUPERVISOR') {
            return;
        }

        $deptoId = Empleado::query()
            ->where('curp', (string) $usuario->curp)
            ->orWhere('nss', (string) $usuario->numero_seguro_social)
            ->value('depto_id');

        if (!$deptoId) {
            $consulta->whereRaw('1 = 0');
            return;
        }

        $consulta->whereHas('empleado', fn ($sub) => $sub->where('depto_id', $deptoId));
    }
}
