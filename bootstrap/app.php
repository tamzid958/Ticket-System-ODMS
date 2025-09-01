<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
//        $middleware->trustProxies(at: [
//            '172.18.0.4',
//            '172.30.30.72',
//            '182.163.124.195',
//        ]);
//
//        $middleware->trustProxies(headers: Request::HEADER_X_FORWARDED_FOR |
//            Request::HEADER_X_FORWARDED_HOST |
//            Request::HEADER_X_FORWARDED_PORT |
//            Request::HEADER_X_FORWARDED_PROTO
//        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
