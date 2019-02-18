<?php

namespace CrCms\Server\Drivers\Laravel\Http\Events;

use CrCms\Server\Server\Events\WorkerStartEvent as BaseWorkerStartEvent;
use Illuminate\Contracts\Http\Kernel;

class WorkerStartEvent extends BaseWorkerStartEvent
{
    /**
     * handle kernel
     *
     * @return void
     */
    public function handle(): void
    {
        parent::handle();

        $this->clearOpcache();

        $kernel = $this->server->getApplication()->make(Kernel::class);

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
