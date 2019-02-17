<?php

namespace CrCms\Server\Drivers\Laravel\Http\Events;

use CrCms\Server\Server\Events\WorkerStartEvent as BaseWorkerStartEvent;

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

        //$this->server
    }
}
