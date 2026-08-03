<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Empleado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AsistenciaController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->string('estado')->toString();
        $fecha = $request->string('fecha')->toString();

        $asistencias = Asistencia::query()
            ->with('empleado')
            ->when($estado !== '', fn ($q) => $q->where('estado', $estado))
            ->when($fecha !== '', fn ($q) => $q->whereDate('fecha', $fecha))
            ->orderByDesc('fecha')
            ->paginate(20)
            ->withQueryString();

        return view('asistencias.index', [
            'asistencias' => $asistencias,
            'filtros' => $request->only(['estado', 'fecha']),
        ]);
    }

    public function create(): View
    {
        return view('asistencias.create', [
            'empleados' => Empleado::query()->where('estatus', 'ACTIVO')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validarDatos($request);
        Asistencia::create($datos);

        return redirect()->route('asistencias.index')->with('exito', 'Asistencia registrada correctamente.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('asistencias.edit', $id);
    }

    public function edit(string $id): View
    {
        return view('asistencias.edit', [
            'asistencia' => Asistencia::findOrFail($id),
            'empleados' => Empleado::query()->where('estatus', 'ACTIVO')->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $asistencia = Asistencia::findOrFail($id);
        $asistencia->update($this->validarDatos($request, $asistencia->id));

        return redirect()->route('asistencias.index')->with('exito', 'Asistencia actualizada correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        Asistencia::findOrFail($id)->delete();

        return redirect()->route('asistencias.index')->with('exito', 'Asistencia eliminada correctamente.');
    }

    private function validarDatos(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],
            'fecha' => [
                'required',
                'date',
                Rule::unique('asistencias', 'fecha')
                    ->where(fn ($q) => $q->where('empleado_id', $request->input('empleado_id')))
                    ->ignore($id),
            ],
            'estado' => ['required', Rule::in(['ASISTENCIA', 'RETARDO', 'FALTA', 'PERMISO', 'VACACIONES'])],
            'horas_trabajadas' => ['required', 'numeric', 'min:0', 'max:24'],
            'origen' => ['nullable', 'string', 'max:40'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
        ]);
    }
}
