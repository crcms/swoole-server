<?php

namespace CrCms\Server\Drivers\Laravel;

use CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Facade;

class Application implements ApplicationContract
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param Container $container
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * initialization application
     *
     * @return Container
     */
    public function app(): Container
    {
        if (is_null($this->app)) {
            $app = $this->createApplication();
            $this->bootstrap($app);
            $this->preload($app);

            $this->app = $app;
        }

        return $this->app;
    }

    /**
     * createNewApplication
     *
     * @return Container
     */
    protected function createApplication(): Container
    {
        return require base_path('bootstrap/app.php');
    }

    /**
     * bootstrap
     *
     * @param Container $app
     * @return void
     */
    protected function bootstrap($app): void
    {
        if (is_laravel($app)) {
            if (!$app->hasBeenBootstrapped()) {
                $app->bootstrapWith([
                    \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
                    \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
                    \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
                    \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
                    \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
                    \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
                    \Illuminate\Foundation\Bootstrap\BootProviders::class,
                ]);
            }
        } elseif (is_lumen($app)) {
            if (method_exists($app, 'boot')) {
                $app->boot();
            }
            if (is_null(Facade::getFacadeApplication())) {
                $app->withFacades();
            }
        } else {
            // @todo Unknown type, temporarily not processed
        }
    }

    /**
     * Preload instance
     *
     * @param Container $app
     * @return void
     */
    protected function preload(Container $app): void
    {
        $preload = $this->config['laravel']['preload'];

        foreach ($preload as $reload) {
            if ($app->has($reload) && !$app->resolved($reload)) {
                $app->make($reload);
            }
        }
    }
}