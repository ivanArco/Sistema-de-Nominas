<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Expediente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        return view('expedientes.create', [
            'empleados' => Empleado::query()->where('estatus', 'ACTIVO')->orderBy('nombre')->get(),
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
        if (Storage::disk('local')->exists($expediente->ruta_archivo)) {
            Storage::disk('local')->delete($expediente->ruta_archivo);
        }

        $expediente->delete();

        return redirect()->route('expedientes.index')->with('exito', 'Documento eliminado correctamente.');
    }
}
