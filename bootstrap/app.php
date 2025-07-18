<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'csrf.handle' => \App\Http\Middleware\HandleCsrfExceptions::class,
            'reports' => \App\Http\Middleware\CheckReportAccess::class,
        ]);
        
        // Agregar el middleware CSRF personalizado a la web
        $middleware->web(append: [
            \App\Http\Middleware\HandleCsrfExceptions::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
