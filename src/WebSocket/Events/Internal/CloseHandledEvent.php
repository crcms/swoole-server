<?php

namespace CrCms\Server\WebSocket\Events\Internal;

/**
 * Class CloseHandledEvent
 * @package CrCms\Server\WebSocket\Events\Internal
 */
class CloseHandledEvent
{
    /**
     * @var int
     */
    public $fd;

    /**
     * CloseHandledEvent constructor.
     * @param int $fd
     */
    public function __construct(int $fd)
    {
        $this->fd = $fd;
    }
}