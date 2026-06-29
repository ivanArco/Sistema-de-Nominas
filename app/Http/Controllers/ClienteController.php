<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClienteController extends Controller
{
    /**
     * Muestra listado de clientes con filtros de consulta.
     */
    public function index(Request $request): View
    {
        $consultaClientes = $this->construirConsultaClientes($request)
            ->orderBy('nombre_comercial');

        $clientes = $consultaClientes->paginate(10)->withQueryString();

        return view('clientes.index', [
            'clientes' => $clientes,
            'filtros' => $request->only(['texto', 'estatus', 'ciudad', 'estado']),
        ]);
    }

    /**
     * Muestra formulario de alta de cliente.
     */
    public function create(): View
    {
        return view('clientes.create');
    }

    /**
     * Guarda un cliente nuevo.
     */
    public function store(Request $request): RedirectResponse
    {
        $datosValidados = $this->validarDatosCliente($request);
        Cliente::create($datosValidados);

        return redirect()->route('clientes.index')->with('exito', 'Cliente registrado correctamente.');
    }

    /**
     * Muestra formulario de edicion.
     */
    public function show(string $id): RedirectResponse
    {
        return redirect()->route('clientes.edit', $id);
    }

    /**
     * Muestra formulario de edicion.
     */
    public function edit(string $id): View
    {
        $cliente = Cliente::findOrFail($id);

        return view('clientes.edit', [
            'cliente' => $cliente,
        ]);
    }

    /**
     * Actualiza cliente existente.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $cliente = Cliente::findOrFail($id);
        $datosValidados = $this->validarDatosCliente($request, $cliente->id);

        $cliente->update($datosValidados);

        return redirect()->route('clientes.index')->with('exito', 'Cliente actualizado correctamente.');
    }

    /**
     * Elimina un cliente solo si hay autorizacion valida de supervisor.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'usuario_supervisor' => ['required', 'string'],
            'contrasena_supervisor' => ['required', 'string'],
        ]);

        $supervisor = User::where('nombre_usuario', $request->string('usuario_supervisor')->toString())
            ->where('rol', 'SUPERVISOR')
            ->where('activo', true)
            ->first();

        if (!$supervisor || !Hash::check($request->string('contrasena_supervisor')->toString(), $supervisor->password)) {
            return redirect()->route('clientes.index')
                ->withInput()
                ->with('error', 'Autorizacion de supervisor no valida. No se elimino el registro.');
        }

        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return redirect()->route('clientes.index')->with('exito', 'Cliente eliminado con autorizacion de supervisor.');
    }

    /**
     * Genera reporte PDF de clientes filtrados.
     */
    public function reportePdf(Request $request)
    {
        $clientes = $this->construirConsultaClientes($request)
            ->orderBy('nombre_comercial')
            ->get();

        $pdf = Pdf::loadView('clientes.reporte_pdf', [
            'clientes' => $clientes,
            'fechaGeneracion' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('reporte_clientes.pdf');
    }

    /**
     * Construye consulta de clientes segun criterios comunes.
     */
    private function construirConsultaClientes(Request $request)
    {
        $texto = $request->string('texto')->trim()->toString();
        $estatus = $request->string('estatus')->toString();
        $ciudad = $request->string('ciudad')->trim()->toString();
        $estado = $request->string('estado')->trim()->toString();

        return Cliente::query()
            ->when($texto !== '', function ($consulta) use ($texto) {
                $consulta->where(function ($subconsulta) use ($texto) {
                    $subconsulta->where('nombre_comercial', 'like', "%{$texto}%")
                        ->orWhere('razon_social', 'like', "%{$texto}%")
                        ->orWhere('nombre_contacto', 'like', "%{$texto}%")
                        ->orWhere('correo_electronico', 'like', "%{$texto}%")
                        ->orWhere('rfc', 'like', "%{$texto}%");
                });
            })
            ->when($estatus !== '', fn ($consulta) => $consulta->where('estatus', $estatus))
            ->when($ciudad !== '', fn ($consulta) => $consulta->where('ciudad', 'like', "%{$ciudad}%"))
            ->when($estado !== '', fn ($consulta) => $consulta->where('estado', 'like', "%{$estado}%"));
    }

    /**
     * Valida datos de alta/edicion de cliente.
     */
    private function validarDatosCliente(Request $request, ?int $clienteId = null): array
    {
        return $request->validate([
            'nombre_comercial' => ['required', 'string', 'max:120'],
            'razon_social' => ['nullable', 'string', 'max:160'],
            'rfc' => ['nullable', 'string', 'max:13', Rule::unique('clientes', 'rfc')->ignore($clienteId)],
            'nombre_contacto' => ['required', 'string', 'max:120'],
            'correo_electronico' => ['nullable', 'email', 'max:120'],
            'telefono_contacto_1' => ['required', 'string', 'max:20'],
            'telefono_contacto_2' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'colonia' => ['nullable', 'string', 'max:120'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'ciudad' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'max:120'],
            'estatus' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);
    }
}
