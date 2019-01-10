<?php

namespace CrCms\Server\WebSocket\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;
use CrCms\Server\Server\Events\StartEvent as BaseStartEvent;
use CrCms\Server\WebSocket\Facades\IO;

/**
 * Class StartEvent
 * @package CrCms\Server\WebSocket\Events
 */
class StartEvent extends BaseStartEvent implements EventContract
{
    /**
     * @param AbstractServer $server
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        // reset db
        IO::getRoom()->reset();
    }
}