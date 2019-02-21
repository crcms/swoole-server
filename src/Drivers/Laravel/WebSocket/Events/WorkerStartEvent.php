<?php

namespace CrCms\Server\Drivers\Laravel\WebSocket\Events;

use CrCms\Server\Drivers\Laravel\Http\Events\WorkerStartEvent as BaseWorkerStartEvent;

class WorkerStartEvent extends BaseWorkerStartEvent
{

    public function handle(): void
    {
        parent::handle();
    }

}