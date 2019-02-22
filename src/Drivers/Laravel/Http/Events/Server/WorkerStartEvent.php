<?php

namespace CrCms\Server\Drivers\Laravel\Http\Events\Server;

use CrCms\Server\Drivers\Laravel\Http\Events\WorkerStartedEvent;
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

        $this->server->getLaravel()->getBaseContainer()->make('events')->dispatch(
            new WorkerStartedEvent($this->server, $this->server->getApplication())
        );
    }
}
