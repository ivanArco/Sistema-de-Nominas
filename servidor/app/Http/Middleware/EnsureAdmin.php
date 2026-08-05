<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (!$usuario) {
            abort(401);
        }

        if (!method_exists($usuario, 'esAdministrador') || !$usuario->esAdministrador()) {
            abort(403, 'Solo el administrador puede acceder a esta seccion.');
        }

        return $next($request);
    }
}
