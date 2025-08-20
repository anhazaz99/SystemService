<?php

use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\AdminOnlyMiddleware;
use App\Http\Middleware\LecturerOnlyMiddleware;
use App\Http\Middleware\ForceJsonResponse;
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
        // Đăng ký middleware đảm bảo JSON response cho API
        $middleware->append(ForceJsonResponse::class);
        
        $middleware->alias([
            'jwt' => JwtMiddleware::class,
            'admin' => AdminOnlyMiddleware::class,
            'lecturer' => LecturerOnlyMiddleware::class,
            'json' => ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withProviders([
        \Modules\Task\app\Providers\TaskServiceProvider::class,
    ])
    ->create();
