<?php

namespace App\Http\Controllers;

use App\Models\User;
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
     * Inicia sesion usando nombre de usuario, correo o CURP.
     */
    public function iniciarSesion(Request $request): RedirectResponse
    {
        $credencialesValidadas = $request->validate([
            'acceso' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'acceso.required' => 'Captura tu usuario, correo o CURP.',
            'password.required' => 'Captura tu contrasena.',
        ]);

        $recordarSesion = $request->boolean('recordarme');

        $acceso = trim($credencialesValidadas['acceso']);
        $contrasena = $credencialesValidadas['password'];

        $credencialesPosibles = [
            ['nombre_usuario' => $acceso, 'password' => $contrasena, 'activo' => true],
            ['email' => $acceso, 'password' => $contrasena, 'activo' => true],
            ['curp' => $acceso, 'password' => $contrasena, 'activo' => true],
        ];

        $inicioExitoso = false;
        foreach ($credencialesPosibles as $credenciales) {
            if (Auth::attempt($credenciales, $recordarSesion)) {
                $inicioExitoso = true;
                break;
            }
        }

        if ($inicioExitoso) {
            $request->session()->regenerate();
            /** @var User $usuario */
            $usuario = $request->user();

            return redirect()->intended(route($this->resolverRutaInicio($usuario)));
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

    private function resolverRutaInicio(User $usuario): string
    {
        if ($usuario->tienePermiso('dashboard.ver')) {
            return 'dashboard';
        }

        if ($usuario->tienePermiso('autoservicio.ver')) {
            return 'mi-cuenta.index';
        }

        if ($usuario->tienePermiso('solicitudes.aprobar')) {
            return 'solicitudes.aprobacion.index';
        }

        return 'login';
    }
}
