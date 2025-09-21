<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Modules\Base\Application\Commands\MakeModule;
use App\Modules\Base\Application\Commands\MakeModuleModel;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
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
