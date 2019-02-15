<?php

namespace CrCms\Server\Server\Events;

use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;

/**
 * Class StartEvent.
 */
class StartEvent extends AbstractEvent implements EventContract
{
    /**
     * @param AbstractServer $server
     */
    public function handle(): void
    {

        parent::setEventProcessName('master');
    }
}
