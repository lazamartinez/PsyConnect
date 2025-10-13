<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'profesional' => \App\Http\Middleware\ProfesionalMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'paciente' => \App\Http\Middleware\PacienteMiddleware::class,
            'verificar.matching' => \App\Http\Middleware\VerificarSistemaMatching::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\VerificarSistemaMatching::class,
        ]);
    })
    ->withCommands([
        \App\Console\Commands\RepararProfesionales::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();

