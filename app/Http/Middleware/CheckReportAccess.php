<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckReportAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Solo administradores y jefes pueden acceder a reportes
        if (!$user || (!$user->isAdministrador() && !$user->isJefe())) {
            abort(403, 'No tienes permisos para acceder a los reportes.');
        }

        return $next($request);
    }
}
