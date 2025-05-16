<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'accountancy.access' => \App\Http\Middleware\AccountancyAccessMiddleware::class,
            'Terbilang' => Riskihajar\Terbilang\Facades\Terbilang::class,
            'Firebase' => Kreait\Laravel\Firebase\Facades\Firebase::class,
            'Excel' => Maatwebsite\Excel\Facades\Excel::class,
            'Agent' => Jenssegers\Agent\Facades\Agent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
