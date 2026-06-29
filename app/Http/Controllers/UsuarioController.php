<?php

namespace App\Http\Controllers;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    /**
     * Muestra el listado de usuarios con filtros de consulta.
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
        ]);
    }

    /**
     * Muestra el formulario de alta.
     */
    public function create(): View
    {
        return view('usuarios.create');
    }

    /**
     * Guarda un usuario nuevo.
     */
    public function store(Request $request): RedirectResponse
    {
        $datosValidados = $this->validarDatosUsuario($request);

        User::create([
            'nombre_usuario' => $datosValidados['nombre_usuario'],
            'name' => $datosValidados['nombre_usuario'],
            'email' => $datosValidados['correo_electronico'],
            'password' => $datosValidados['contrasena'],
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
            'rol' => $datosValidados['rol'],
            'activo' => isset($datosValidados['activo']) ? (bool) $datosValidados['activo'] : true,
        ]);

        return redirect()->route('usuarios.index')->with('exito', 'Usuario registrado correctamente.');
    }

    /**
     * Muestra formulario de edicion.
     */
    public function show(string $id): RedirectResponse
    {
        return redirect()->route('usuarios.edit', $id);
    }

    /**
     * Muestra formulario de edicion.
     */
    public function edit(string $id): View
    {
        $usuario = User::findOrFail($id);

        return view('usuarios.edit', [
            'usuario' => $usuario,
        ]);
    }

    /**
     * Actualiza los datos de un usuario.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $usuario = User::findOrFail($id);
        $datosValidados = $this->validarDatosUsuario($request, $usuario->id);

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
            'rol' => $datosValidados['rol'],
            'activo' => isset($datosValidados['activo']) ? (bool) $datosValidados['activo'] : false,
        ]);

        if (!empty($datosValidados['contrasena'])) {
            $usuario->password = $datosValidados['contrasena'];
        }

        $usuario->save();

        return redirect()->route('usuarios.index')->with('exito', 'Usuario actualizado correctamente.');
    }

    /**
     * Elimina un usuario solo si existe autorizacion valida de supervisor.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'usuario_supervisor' => ['required', 'string'],
            'contrasena_supervisor' => ['required', 'string'],
        ], [
            'usuario_supervisor.required' => 'Debe indicar el usuario supervisor para autorizar la eliminacion.',
            'contrasena_supervisor.required' => 'Debe capturar la contrasena del supervisor.',
        ]);

        $supervisor = User::where('nombre_usuario', $request->string('usuario_supervisor')->toString())
            ->where('rol', 'SUPERVISOR')
            ->where('activo', true)
            ->first();

        if (!$supervisor || !Hash::check($request->string('contrasena_supervisor')->toString(), $supervisor->password)) {
            return redirect()->route('usuarios.index')
                ->withInput()
                ->with('error', 'Autorizacion de supervisor no valida. No se elimino el registro.');
        }

        $usuario = User::findOrFail($id);
        $usuario->delete();

        return redirect()->route('usuarios.index')->with('exito', 'Usuario eliminado con autorizacion de supervisor.');
    }

    /**
     * Genera un reporte PDF segun los filtros de busqueda activos.
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
            ->when($fechaHasta !== '', fn ($consulta) => $consulta->whereDate('fecha_contratacion', '<=', $fechaHasta));
    }

    /**
     * Valida datos de alta/edicion de usuario.
     */
    private function validarDatosUsuario(Request $request, ?int $usuarioId = null): array
    {
        $reglasContrasena = $usuarioId
            ? ['nullable', 'string', 'min:8', 'confirmed']
            : ['required', 'string', 'min:8', 'confirmed'];

        return $request->validate([
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
            'rol' => ['required', Rule::in(['ADMIN', 'NOMINISTA', 'SUPERVISOR', 'CONSULTA'])],
            'activo' => ['nullable', 'boolean'],
        ], [
            'contrasena.confirmed' => 'La confirmacion de contrasena no coincide.',
        ]);
    }
}
