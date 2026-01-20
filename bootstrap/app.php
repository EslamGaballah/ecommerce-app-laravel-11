<?php

use App\Http\Middleware\CheckUserType;
use App\Http\Middleware\SetAppLang;
use App\Http\Middleware\UpdateUserLastActiveAt;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->append(CheckUserType::class); // append middleware during every http request
        // $middleware->append( SetAppLang::class); 
        $middleware->web(append:[
            SetAppLang::class,
             UpdateUserLastActiveAt::class,
        ]); // append middleware during every http request

        $middleware->api(append: [
            SetAppLang::class, 
        ]);

        $middleware->alias([
        'auth.role' => CheckUserType::class
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
