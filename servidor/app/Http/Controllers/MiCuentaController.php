<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\EmpleadoHistorial;
use App\Models\Incidencia;
use App\Models\Nomina;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MiCuentaController extends Controller
{
    public function index(): View
    {
        /** @var User $usuario */
        $usuario = Auth::user();
        $empleado = $this->resolverEmpleadoDelUsuario();

        $ultimasNominas = collect();
        $historialLaboral = collect();

        if ($empleado) {
            $ultimasNominas = Nomina::query()
                ->with('periodo')
                ->where('empleado_id', $empleado->id)
                ->latest()
                ->limit(5)
                ->get();

            $historialLaboral = EmpleadoHistorial::query()
                ->with('puesto')
                ->where('empleado_id', $empleado->id)
                ->orderByDesc('fecha_movimiento')
                ->limit(5)
                ->get();
        }

        $vacacionesDisponibles = $this->calcularVacacionesDisponibles($empleado);

        return view('mi_cuenta.index', [
            'usuario' => $usuario,
            'empleado' => $empleado,
            'ultimasNominas' => $ultimasNominas,
            'historialLaboral' => $historialLaboral,
            'vacacionesDisponibles' => $vacacionesDisponibles,
        ]);
    }

    public function edit(): View
    {
        /** @var User $usuario */
        $usuario = Auth::user();

        return view('mi_cuenta.edit', [
            'usuario' => $usuario,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $usuario */
        $usuario = Auth::user();

        $datos = $request->validate([
            'email' => ['required', 'email', 'max:120', 'unique:users,email,'.$usuario->id],
            'telefono_contacto_1' => ['required', 'string', 'max:20'],
            'telefono_contacto_2' => ['nullable', 'string', 'max:20'],
            'direccion' => ['required', 'string', 'max:200'],
            'colonia' => ['required', 'string', 'max:120'],
            'codigo_postal' => ['required', 'string', 'max:10'],
            'ciudad' => ['required', 'string', 'max:120'],
            'estado' => ['required', 'string', 'max:120'],
        ]);

        $usuario->update($datos);

        return redirect()->route('mi-cuenta.index')->with('exito', 'Datos personales actualizados correctamente.');
    }

    public function recibos(): View
    {
        $empleado = $this->resolverEmpleadoDelUsuario();

        abort_if(!$empleado, 404, 'No hay empleado vinculado para este usuario.');

        $nominas = Nomina::query()
            ->with(['periodo'])
            ->where('empleado_id', $empleado->id)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('mi_cuenta.recibos', [
            'empleado' => $empleado,
            'nominas' => $nominas,
        ]);
    }

    public function reciboPdf(Nomina $nomina)
    {
        $empleado = $this->resolverEmpleadoDelUsuario();

        abort_if(!$empleado || (int) $nomina->empleado_id !== (int) $empleado->id, 403, 'No puedes acceder a este recibo.');

        $nomina->load(['empleado', 'periodo', 'detalles.concepto']);

        $pdf = Pdf::loadView('reportes.nominas_pdf', [
            'nominas' => collect([$nomina]),
            'filtros' => [],
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
            'empresaNombre' => (string) config('app.name', 'Sistema de Nomina'),
        ])->setPaper('letter', 'portrait');

        return $pdf->download('recibo_nomina_'.$nomina->id.'.pdf');
    }

    public function passwordForm(): View
    {
        return view('mi_cuenta.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'contrasena_actual' => ['required', 'string'],
            'contrasena_nueva' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var User $usuario */
        $usuario = Auth::user();

        if (!Hash::check($datos['contrasena_actual'], (string) $usuario->password)) {
            throw ValidationException::withMessages([
                'contrasena_actual' => 'La contrasena actual no coincide.',
            ]);
        }

        $usuario->password = $datos['contrasena_nueva'];
        $usuario->save();

        return redirect()->route('mi-cuenta.password.form')->with('exito', 'Contrasena actualizada correctamente.');
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

    private function calcularVacacionesDisponibles(?Empleado $empleado): float
    {
        if (!$empleado) {
            return 0.0;
        }

        $aniosServicio = max(1, (int) optional($empleado->f_ingreso)->diffInYears(now()));
        $baseAnual = 12 + max(0, $aniosServicio - 1) * 2;

        $diasTomados = (float) Incidencia::query()
            ->where('empleado_id', $empleado->id)
            ->whereIn('tipo', ['VACACIONES', 'VACACIONES_PAGADAS'])
            ->sum('cantidad');

        return max(0.0, round($baseAnual - $diasTomados, 2));
    }
}
