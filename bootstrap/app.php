<?php

use App\Modules\Base\Application\Commands\MakeModule;
use App\Modules\Base\Application\Commands\MakeModuleModel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['middleware' => ['api', 'auth:employee']],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withCommands([
        MakeModule::class,
        MakeModuleModel::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
