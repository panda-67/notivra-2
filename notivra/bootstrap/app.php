<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocaleFromCookie;
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
            'role' => RoleMiddleware::class,
        ]);

        $middleware->append([
            SetLocaleFromCookie::class,
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->registered(function ($app) {
        if ($app->environment('production')) {
            $publicPath = realpath(base_path('/../../public_html/notivra'));

            if ($publicPath !== false) {
                $app->usePublicPath($publicPath . DIRECTORY_SEPARATOR);
            }
        }

        $app->usePublicPath(path: realpath(base_path('../public_html')));
    })->create();
