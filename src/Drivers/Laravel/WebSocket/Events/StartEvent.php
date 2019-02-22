<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\Drivers\Laravel\Facades\IO;
use CrCms\Server\Server\Events\StartEvent as BaseStartEvent;

/**
 * Class StartEvent.
 */
class StartEvent extends BaseStartEvent
{
    public function handle(): void
    {
        parent::handle();

        // reset db
        IO::getRoom()->reset();
    }
}
