<?php

namespace CrCms\Server\Server\Events;

/**
 * Class StartEvent.
 */
class StartEvent extends AbstractEvent
{
    /**
     * handle kernel
     *
     * @return void
     */
    public function handle(): void
    {
        parent::setEventProcessName('master');
    }
}
