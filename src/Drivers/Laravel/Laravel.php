<?php

namespace CrCms\Server\Drivers\Laravel;

use CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Facade;

class Laravel
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $resetters = [];

    /**
     * @param ApplicationContract $contract
     */
    public function __construct(ApplicationContract $contract)
    {
        $this->setBaseContainer($contract::application());
        $this->initApplication();
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
        if (is_null($this->app)) {
            $this->resetApplication();
        }

        return $this->app;
    }

    /**
     * open
     *
     * @return void
     */
    public function open(): void
    {
        $this->getApplication();

        $this->bindApplication();

        $this->resetResetters();

        Facade::clearResolvedInstances();
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        $this->resetApplication();
    }


    /**
     * Preload instance
     *
     * @return void
     */
    public function preload(): void
    {
        $preload = $this->container['config']->get('swoole.laravel.preload', []);

        foreach ($preload as $reload) {
            $this->app->make($reload);
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
     * bindApplication
     *
     * @return void
     */
    protected function bindApplication(): void
    {
        $this->app->instance('app', $this->app);
        $this->app->instance(Container::class, $this->app);
        $this->app::setInstance($this->app);
        //\Illuminate\Container\Container::setInstance($this->app);
        Facade::setFacadeApplication($this->app);
    }

    /**
     * initApplication
     *
     * @return void
     */
    protected function initApplication(): void
    {
        $this->resetApplication();

        $this->prepareResetters();
    }

    /**
     * resetApplication
     *
     * @return void
     */
    protected function resetApplication(): void
    {
        $this->app = clone $this->container;
    }

    /**
     * prepareResetters
     *
     * @return void
     */
    protected function prepareResetters(): void
    {
        $resetters = $this->container['config']->get('swoole.laravel.resetters');

        foreach ($resetters as $resetter) {
            $this->resetters[] = $this->container->make($resetter);
        }
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
     * @return void
     */
    protected function resetResetters(): void
    {
        $app = $this->getApplication();

        foreach ($this->resetters as $resetter) {
            $resetter->handle($app, $this);
        }
    }
}