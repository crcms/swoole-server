<?php

namespace CrCms\Server\Drivers\Laravel\WebSocket\Events\Internal;

/**
 * Class CloseHandledEvent.
 */
class CloseHandledEvent
{
    /**
     * @var int
     */
    public $fd;

    /**
     * CloseHandledEvent constructor.
     *
     * @param int $fd
     */
    public function __construct(int $fd)
    {
        $this->fd = $fd;
    }
}
