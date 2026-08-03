<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermisoController extends Controller
{
    public function index(Request $request): View
    {
        $texto = $request->string('texto')->trim()->toString();

        $permisos = Permiso::query()
            ->withCount('roles')
            ->when($texto !== '', function ($query) use ($texto) {
                $query->where('clave', 'like', "%{$texto}%")
                    ->orWhere('nombre', 'like', "%{$texto}%");
            })
            ->orderBy('clave')
            ->paginate(25)
            ->withQueryString();

        return view('permisos.index', [
            'permisos' => $permisos,
            'filtros' => $request->only(['texto']),
        ]);
    }

    public function create(): View
    {
        return view('permisos.create', [
            'permiso' => new Permiso(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'clave' => ['required', 'string', 'max:120', 'unique:permisos,clave'],
            'nombre' => ['required', 'string', 'max:150'],
        ]);

        Permiso::create($datos);

        return redirect()->route('permisos.index')->with('exito', 'Permiso creado correctamente.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('permisos.edit', $id);
    }

    public function edit(string $id): View
    {
        return view('permisos.edit', [
            'permiso' => Permiso::findOrFail($id),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $permiso = Permiso::findOrFail($id);

        $datos = $request->validate([
            'clave' => ['required', 'string', 'max:120', Rule::unique('permisos', 'clave')->ignore($permiso->id)],
            'nombre' => ['required', 'string', 'max:150'],
        ]);

        $permiso->update($datos);

        return redirect()->route('permisos.index')->with('exito', 'Permiso actualizado correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        Permiso::findOrFail($id)->delete();

        return redirect()->route('permisos.index')->with('exito', 'Permiso eliminado correctamente.');
    }
}
