<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;

class HandleCsrfExceptions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (TokenMismatchException $e) {
            // Si es una petición AJAX, devolver JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Tu sesión ha expirado. Por favor, recarga la página.',
                    'error' => 'token_mismatch'
                ], 419);
            }

            // Para peticiones normales, redirigir con mensaje amigable
            return redirect()->back()
                ->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->withErrors(['csrf' => 'Tu sesión ha expirado. Por favor, intenta nuevamente.']);
        }
    }
}
