<?php

namespace CrCms\Server\Drivers\Laravel;

use CrCms\Server\Coroutine\Context;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Facade;

class Laravel
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $resetters = [];

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @param Container $container
     */
    public function __construct(Container $container, Repository $config)
    {
        $this->setConfig($config);
        $this->setBaseContainer($container);
        $this->initialize();
    }

    /**
     * getBaseContainer
     *
     * @return Container
     */
    public function getBaseContainer(): Container
    {
        return $this->container;
    }

    /**
     * getApplication
     *
     * @return Container
     */
    public function getApplication(): Container
    {
        $app = Context::get('app');
        if (is_null($app)) {
            return $this->createNewApplication();
        }

        return $app;
    }

    /**
     * open
     *
     * @return void
     */
    public function open(): void
    {
        $app = $this->getApplication();

        $this->bindApplication($app);

        $this->resetResetters($app);

        Facade::clearResolvedInstances();
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        Context::delete('app');
    }

    /**
     * Preload instance
     *
     * @return void
     */
    public function preload(): void
    {
        $preload = $this->config->get('swoole.laravel.preload', []);

        $app = $this->getApplication();

        foreach ($preload as $reload) {
            if ($app->has($reload) && !$app->resolved($reload)) {
                $app->make($reload);
            }
        }
    }

    /**
     * getResetters
     *
     * @return array
     */
    public function getResetters(): array
    {
        return $this->resetters;
    }

    /**
     * getConfig
     *
     * @return Repository
     */
    public function getConfig(): Repository
    {
        return $this->config;
    }

    /**
     * bindApplication
     *
     * @param Container $app
     * @return void
     */
    protected function bindApplication(Container $app): void
    {
        $app->instance('app', $app);
        $app->instance(Container::class, $app);
        $app::setInstance($app);
        Facade::setFacadeApplication($app);
    }

    /**
     * initialize
     *
     * @return void
     */
    protected function initialize(): void
    {
        $this->prepareResetters();

        $this->createNewApplication();
    }

    /**
     * createNewApplication
     *
     * @return Container
     */
    protected function createNewApplication(): Container
    {
        $app = clone $this->container;
        Context::put('app', $app);

        return $app;
    }

    /**
     * prepareResetters
     *
     * @return void
     */
    protected function prepareResetters(): void
    {
        $resetters = $this->config->get('swoole.laravel.resetters');

        foreach ($resetters as $resetter) {
            $this->resetters[] = $this->container->make($resetter);
        }
    }

    /**
     * setConfig
     *
     * @param Repository $config
     * @return void
     */
    protected function setConfig(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * setBaseContainer
     *
     * @param Container $container
     * @return void
     */
    protected function setBaseContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * resetResetters
     *
     * @param Container $app
     * @return void
     */
    protected function resetResetters(Container $app): void
    {
        foreach ($this->resetters as $resetter) {
            $resetter->handle($app, $this);
        }
    }
}