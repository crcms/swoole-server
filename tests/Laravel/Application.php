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
    /**
     * initialization
     *
     * @param string $basePath
     * @return void
     */
    protected function initialization(string $basePath): void
    {
        mkdir($basePath.'/bootstrap/cache', 0777, true);
        mkdir($basePath.'/config', 0777, true);
        file_put_contents($basePath.'/config/app.php', '<?php'.PHP_EOL.var_export([], true).';');
    }

    protected function createApplication(): Container
    {
        $basePath = __DIR__.'/../tmp/laravel';

        $this->initialization($basePath);

        $app = new LaravelApplication(
            $basePath
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