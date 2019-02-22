<?php

namespace CrCms\Server\Drivers\Laravel\Http\Events;

use CrCms\Server\Drivers\Laravel\Http\Server;
use CrCms\Server\Server\Events\WorkerStartEvent as BaseWorkerStartEvent;

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

        $this->server->getLaravel()->getBaseContainer()->make('events')->dispatch('worker_start', [$this->server, $this->server->getApplication()]);
    }
}
