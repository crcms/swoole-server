<?php

namespace CrCms\Server\Server\Events;

use CrCms\Server\Server\AbstractServer;

/**
 * Class StartEvent.
 */
class StartEvent extends AbstractEvent
{
    /**
     * @param AbstractServer $server
     */
    public function handle(): void
    {
        parent::setEventProcessName('master');
    }
}
