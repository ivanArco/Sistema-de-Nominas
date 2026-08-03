<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RolController extends Controller
{
    public function index(Request $request): View
    {
        $texto = $request->string('texto')->trim()->toString();

        $roles = Rol::query()
            ->withCount('usuarios')
            ->with('permisos:id,clave')
            ->when($texto !== '', function ($query) use ($texto) {
                $query->where('clave', 'like', "%{$texto}%")
                    ->orWhere('nombre', 'like', "%{$texto}%");
            })
            ->orderBy('clave')
            ->paginate(20)
            ->withQueryString();

        return view('roles.index', [
            'roles' => $roles,
            'filtros' => $request->only(['texto']),
        ]);
    }

    public function create(): View
    {
        return view('roles.create', [
            'rol' => new Rol(),
            'permisos' => Permiso::query()->orderBy('clave')->get(),
            'seleccionados' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'clave' => ['required', 'string', 'max:60', 'regex:/^[A-Z0-9_]+$/', 'unique:roles,clave'],
            'nombre' => ['required', 'string', 'max:120'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['integer', 'exists:permisos,id'],
        ]);

        $rol = Rol::create([
            'clave' => Str::upper(trim((string) $datos['clave'])),
            'nombre' => $datos['nombre'],
        ]);

        $rol->permisos()->sync($datos['permisos'] ?? []);

        return redirect()->route('roles.index')->with('exito', 'Rol creado correctamente.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('roles.edit', $id);
    }

    public function edit(string $id): View
    {
        $rol = Rol::query()->with('permisos:id')->findOrFail($id);

        return view('roles.edit', [
            'rol' => $rol,
            'permisos' => Permiso::query()->orderBy('clave')->get(),
            'seleccionados' => $rol->permisos->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $rol = Rol::findOrFail($id);

        $datos = $request->validate([
            'clave' => ['required', 'string', 'max:60', 'regex:/^[A-Z0-9_]+$/', Rule::unique('roles', 'clave')->ignore($rol->id)],
            'nombre' => ['required', 'string', 'max:120'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['integer', 'exists:permisos,id'],
        ]);

        $rol->update([
            'clave' => Str::upper(trim((string) $datos['clave'])),
            'nombre' => $datos['nombre'],
        ]);

        $rol->permisos()->sync($datos['permisos'] ?? []);

        return redirect()->route('roles.index')->with('exito', 'Rol actualizado correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $rol = Rol::query()->withCount('usuarios')->findOrFail($id);

        if ($rol->usuarios_count > 0) {
            return back()->with('error', 'No se puede eliminar un rol con usuarios asignados.');
        }

        $rol->delete();

        return redirect()->route('roles.index')->with('exito', 'Rol eliminado correctamente.');
    }
}
