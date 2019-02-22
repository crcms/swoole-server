<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\Drivers\Laravel\Facades\IO;
use CrCms\Server\Server\Events\StartEvent as BaseStartEvent;

class StartEvent extends BaseStartEvent
{
    /**
     * handle
     *
     * @return void
     */
    public function handle(): void
    {
        parent::handle();

        // reset db
        IO::getRoom()->reset();
    }
}
