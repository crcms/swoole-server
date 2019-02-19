<?php

namespace CrCms\Server\Drivers\Laravel\Http\Events;

use CrCms\Server\Drivers\Laravel\Http\Server;
use CrCms\Server\Drivers\Laravel\Laravel;
use CrCms\Server\Server\Events\WorkerStartEvent as BaseWorkerStartEvent;
use Illuminate\Contracts\Http\Kernel;

class WorkerStartEvent extends BaseWorkerStartEvent
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * handle kernel
     *
     * @return void
     */
    public function handle(): void
    {
        parent::handle();

        $this->clearOpcache();

        $appClass = $this->server->getConfig()['swoole']['laravel']['app'];
        $laravel = new Laravel($appClass::app());

        $this->app->singleton('server.laravel', function ($app) {
            return new Laravel($app->make(ApplicationContract::class));
        });

        // @todo 这块还是有问题，在worker中的app，还需要考虑清楚，以及preload
        // @todo 在Request中是每次都是新的app，而非worker中的app

        //preload instance
        $this->server->getLaravel()->preload();

        $kernel = $this->server->getLaravel()->getBaseContainer()->make(Kernel::class);

        $kernel->bootstrap();
    }

    /**
     * Clear APC or OPCache.
     */
    protected function clearOpcache()
    {
        if (extension_loaded('apc')) {
            apc_clear_cache();
        }

        if (extension_loaded('Zend OPcache')) {
            opcache_reset();
        }
    }
}
