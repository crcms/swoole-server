<?php

namespace CrCms\Server\Drivers\Laravel\Http\Events;

use function CrCms\Server\clear_opcache;
use CrCms\Server\Drivers\Laravel\Http\Server;
use CrCms\Server\Server\Events\WorkerStartEvent as BaseWorkerStartEvent;
use Illuminate\Contracts\Container\Container;
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

        clear_opcache();

        $app = $this->server->getApplication();
//
//        $this->bootstrap($app);
//
//        //preload sharing instance
//        $this->server->getLaravel()->preload();

        $this->server->getContainer()->make('events')->dispatch('worker_start', [$this->server, $app]);
    }

    /**
     * bootstrap
     *
     * @param Container $app
     * @return void
     */
    protected function bootstrap(Container $app): void
    {
        $app->make(Kernel::class)->bootstrap();
    }
}
