<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AutenticacionController extends Controller
{
    /**
     * Muestra la vista de acceso al sistema.
     */
    public function mostrarLogin(): View
    {
        return view('autenticacion.login');
    }

    /**
     * Inicia sesion usando nombre de usuario o correo.
     */
    public function iniciarSesion(Request $request): RedirectResponse
    {
        $credencialesValidadas = $request->validate([
            'acceso' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'acceso.required' => 'Captura tu usuario o correo electronico.',
            'password.required' => 'Captura tu contrasena.',
        ]);

        $recordarSesion = $request->boolean('recordarme');
        $campoAcceso = filter_var($credencialesValidadas['acceso'], FILTER_VALIDATE_EMAIL) ? 'email' : 'nombre_usuario';

        $credenciales = [
            $campoAcceso => $credencialesValidadas['acceso'],
            'password' => $credencialesValidadas['password'],
            'activo' => true,
        ];

        if (Auth::attempt($credenciales, $recordarSesion)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('acceso'))
            ->withErrors([
                'acceso' => 'Credenciales invalidas o usuario inactivo.',
            ]);
    }

    /**
     * Cierra sesion del usuario autenticado.
     */
    public function cerrarSesion(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('exito', 'Sesion cerrada correctamente.');
    }
}
