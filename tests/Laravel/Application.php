<?php

namespace CrCms\Server\Tests\Laravel;

use CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract;
use CrCms\Server\Drivers\Laravel\SwooleServiceProvider;
use Illuminate\Contracts\Container\Container;
use CrCms\Server\Drivers\Laravel\Application as BaseApplication;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;

class Application extends BaseApplication implements ApplicationContract
{
    protected function createApplication(): Container
    {
        $app = new LaravelApplication(
            __DIR__
        );
        //$app->instance('path.config',__DIR__.'/../../config');
        $app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \Illuminate\Foundation\Http\Kernel::class
        );
        $app->singleton(
            Kernel::class,
            \Illuminate\Foundation\Console\Kernel::class
        );
        $app->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        $this->bootstrap($app);
        $app->register(SwooleServiceProvider::class);

        return $app;

    }
}